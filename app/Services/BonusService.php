<?php
namespace App\Services;

use App\Models\User;
use Exception;

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
}
