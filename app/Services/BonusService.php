<?php
namespace App\Services;

use App\Models\Bonus;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;

class BonusService
{
    public function getUserBonusSummary(User $user): array
    {
        $activeQuery = $user->bonuses()->notExpired()->active();
        $futureQuery = $user->bonuses()->notExpired()->future();

        return [
            'active_total' => (clone $activeQuery)->sum('amount'),
            'active' => $activeQuery->orderBy('available_from')->get(),
            'future' => $futureQuery->orderBy('available_from')->get(),
        ];
    }

    /**
     * @throws Exception
     */
    public function validate(User $user, int $amount): void
    {
        $available = $user->bonuses()
            ->notExpired()
            ->active()
            ->sum('amount');

        if ($amount > $available) {
            throw new Exception('Not enough available bonuses');
        }
    }

    public function getAvailableAmount(User $user): int
    {
        return $user->bonuses()
            ->notExpired()
            ->active()
            ->sum('amount');
    }

    public function spend(User $user, int $amount): void
    {
        if ($amount <= 0) {
            return;
        }

        DB::transaction(function () use ($user, $amount) {
            $bonuses = $user->bonuses()
                ->notExpired()
                ->active()
                ->where('amount', '>', 0)
                ->orderBy('expires_at')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            $available = $bonuses->sum(
                fn (Bonus $bonus) => (float) $bonus->amount
            );

            if ($amount > $available) {
                throw new Exception('Not enough available bonuses.');
            }

            $remaining = $amount;

            foreach ($bonuses as $bonus) {
                if ($remaining <= 0) {
                    break;
                }

                $bonusAmount = (int) $bonus->amount;
                $spent = min($bonusAmount, $remaining);

                $bonus->decrement('amount', $spent);

                $remaining -= $spent;
            }
        });
    }
}
