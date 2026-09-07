<?php
namespace App\Jobs;

use App\Enums\IntegrationErrorCode;
use App\Models\Bonus;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ProcessBatchBonusesJob extends ProcessBatchJob
{
    protected function lockKey(): string
    {
        return 'bonuses-batch-import';
    }

    protected function updateChunk(array $items): array
    {
        $processed = 0;
        $failed = 0;
        $grouped = [];

        foreach ($items as $index => $item) {
            $errors = $this->validateItem($item);

            if ($errors) {
                $failed++;

                foreach ($errors as $error) {
                    $this->logError(
                        index: $index,
                        code: $error['code']->value,
                        message: $error['message'],
                        field: $error['field'],
                        externalId: $item['id'] ?? null,
                    );
                }

                continue;
            }

            if (!is_array($item['bonuses'])) {
                $this->logError(
                    index: $index,
                    code: IntegrationErrorCode::InvalidValue->value,
                    message: 'Bonuses should be an array',
                    field: 'bonuses',
                    externalId: $item['id'] ?? null,
                );

                $failed++;

                continue;
            }

            $grouped[$item['phone']][] = $item['bonuses'];
        }

        if (empty($grouped)) {
            $this->failBatch('Not found users');

            return [$processed, $failed];
        }

        $users = User::query()
            ->whereIn('phone', array_keys($grouped))
            ->get(['id', 'phone'])
            ->keyBy('phone');

        foreach ($grouped as $phone => $bonusSets) {
            $user = $users->get($phone);

            if (!$user) {
                $this->logError(
                    index: 0,
                    code: IntegrationErrorCode::Exception->value,
                    message: 'Not found user',
                    externalId: $phone,
                );

                $failed++;

                continue;
            }

            $rows = [];

            $bonuses = collect($bonusSets)
                ->flatten(1)
                ->values();

            foreach ($bonuses as $index => $bonus) {
                $errors = $this->validateItem($bonus, $this->innerRules());

                if ($errors) {
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

                if ($rows) {
                    Bonus::insert($rows);
                }
            });

            $processed++;
        }

        return [$processed, $failed];
    }

    protected function rules(): array
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

    protected function innerRules(): array
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
}
