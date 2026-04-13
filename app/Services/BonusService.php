<?php
namespace App\Services;

use App\Models\User;

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
}
