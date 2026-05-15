<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CityResource;
use App\Models\City;

class CityController extends Controller
{
    public function cities()
    {
        $cities = City::all()->load('region');

        return response()->json([
            'data' => CityResource::collection($cities),
        ]);
    }
}
