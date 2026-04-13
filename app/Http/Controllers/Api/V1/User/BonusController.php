<?php
namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\BonusResource;
use App\Services\BonusService;
use Illuminate\Http\Request;

class BonusController extends Controller
{
    public function __invoke(Request $request, BonusService $service)
    {
        $data = $service->getUserBonusSummary($request->user());

        return response()->json([
            'active_total' => $data['active_total'],
            'active_bonuses' => BonusResource::collection($data['active']),
            'future_bonuses' => BonusResource::collection($data['future']),
        ]);
    }
}
