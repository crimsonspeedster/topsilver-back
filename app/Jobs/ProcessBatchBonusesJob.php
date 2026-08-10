<?php
namespace App\Jobs;

use App\Enums\IntegrationBatchStatus;
use App\Enums\IntegrationErrorCode;
use App\Models\Bonus;
use App\Models\IntegrationBatch;
use App\Models\IntegrationBatchError;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

class ProcessBatchBonusesJob implements ShouldQueue
{
    use Dispatchable, Queueable, InteractsWithQueue, SerializesModels;

    public function __construct(
        public IntegrationBatch $batch
    ) {}

    /**
     * @throws Throwable
     */
    public function handle(): void
    {
        $lock = Cache::lock('bonuses-batch-import', 600);

        if (!$lock->get()) {
            return;
        }

        try {
            $this->batch->update([
                'status' => IntegrationBatchStatus::Processing,
                'processed_count' => 0,
                'failed_count' => 0,
                'started_at' => now(),
            ]);

            $data = json_decode($this->batch->payload, true);

            if (!is_array($data) || empty($data)) {
                $this->failBatch('Empty payload');
                return;
            }

            $items = $data['items'] ?? [];

            if (!is_array($items) || empty($items)) {
                $this->failBatch('Empty items');
                return;
            }

            $processed = 0;
            $failed = 0;

            collect($items)
                ->chunk(200)
                ->each(function ($chunk) use (&$processed, &$failed) {
                    [$p, $f] = $this->processChunk($chunk->toArray());
                    $processed += $p;
                    $failed += $f;
                });

            $status = $failed > 0 ? IntegrationBatchStatus::PartialFailed : IntegrationBatchStatus::Completed;

            $this->batch->update([
                'status' => $status,
                'processed_count' => $processed,
                'failed_count' => $failed,
                'items_count' => count($items),
                'finished_at' => now(),
            ]);
        }
        catch (Throwable $e) {
            $this->failBatch($e->getMessage());

            throw $e;
        }
        finally {
            optional($lock)->release();
        }
    }

    /**
     * @throws Throwable
     */
    private function processChunk(array $items): array
    {
        $processed = 0;
        $failed = 0;
        $grouped = [];

        foreach ($items as $index => $item) {
            $errors = $this->validateItem($item, $this->rulesGlobal());

            if (!empty($errors)) {
                $failed++;

                foreach ($errors as $error) {
                    $this->logError(
                        index: $index,
                        code: $error['code']->value,
                        message: $error['message'],
                        field: $error['field'],
                        externalId: $item['id'] ?: null,
                    );
                }

                continue;
            }

            if (
                !is_array($item['bonuses'])
            ) {
                $this->logError(
                    index: $index,
                    code: IntegrationErrorCode::InvalidValue->value,
                    message: 'Bonuses should be an array',
                    field: 'bonuses',
                    externalId: $item['id'] ?: null,
                );

                $failed++;
                continue;
            }

            $grouped[$item['phone']][] = $item['bonuses'];
            $processed++;
        }

        if (empty($grouped)) {
            $this->failBatch('Not found users');

            return [$processed, $failed];
        }

        $phones = array_keys($grouped);

        $users = User::query()
            ->with(['bonuses'])
            ->whereIn('phone', $phones)
            ->get(['id', 'phone'])
            ->keyBy('phone');

        foreach ($grouped as $phone => $bonusSets) {
            $user = $users->get($phone);

            if (!$user) {
                $this->logError(
                    index: 0,
                    code: IntegrationErrorCode::Exception->value,
                    message: 'Not found user',
                );

                $failed++;
                continue;
            }

            $bonuses = collect($bonusSets)->flatten(1)->values();

            $rows = [];

            foreach ($bonuses as $index => $bonus) {
                $errors = $this->validateItem($bonus, $this->rulesInner());

                if (!empty($errors)) {
                    $failed++;

                    foreach ($errors as $error) {
                        $this->logError(
                            index: $index,
                            code: $error['code']->value,
                            message: $error['message'],
                            field: $error['field'],
                        );
                    }

                    continue;
                }


                $rows[] = [
                    'user_id' => $user->id,
                    'amount' => $bonus['amount'],
                    'accrual_from' => $bonus['accrual_from'],
                    'available_from' => $bonus['available_from'],
                    'expires_at' => $bonus['expires_at'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            DB::transaction(function () use ($user, $rows) {
                Bonus::where('user_id', $user->id)->delete();

                if (!empty($rows)) {
                    Bonus::insert($rows);
                }
            });

            $processed++;
        }

        return [$processed, $failed];
    }

    private function failBatch(string $message): void
    {
        $this->batch->update([
            'status' => IntegrationBatchStatus::Failed,
            'error_message' => $message,
        ]);
    }

    private function logError(
        int $index,
        string $code,
        string $message,
        ?string $field = null,
        ?string $externalId = null,
    ): void {
        IntegrationBatchError::create([
            'integration_batch_id' => $this->batch->id,
            'item_index' => $index,
            'external_id' => $externalId,
            'field' => $field,
            'code' => $code,
            'message' => $message,
        ]);
    }

    private function rulesGlobal(): array
    {
        return [
            'phone' => [
                'required' => true,
            ],
            'bonuses' => [
                'required' => true,
            ],
        ];
    }

    private function rulesInner(): array
    {
        return [
            'amount' => [
                'required' => true,
            ],
            'accrual_from' => [
                'required' => true,
            ],
            'available_from' => [
                'required' => true,
            ],
            'expires_at' => [
                'required' => true,
            ],
        ];
    }

    private function validateItem(array $item, array $rules): array
    {
        $errors = [];

        foreach ($rules as $field => $fieldRules) {
            $value = $item[$field] ?? null;

            $valueStr = is_string($value) ? trim($value) : $value;

            if (($fieldRules['required'] ?? false) && empty($valueStr)) {
                $errors[] = [
                    'field' => $field,
                    'code' => IntegrationErrorCode::Required,
                    'message' => ucfirst($field) . ' is required',
                ];
            }
        }

        return $errors;
    }
}
