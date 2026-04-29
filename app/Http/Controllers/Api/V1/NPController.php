<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\NPAreaResource;
use App\Http\Resources\NPCityResource;
use App\Http\Resources\NPWarehouseResource;
use App\Http\Resources\PaginationResource;
use App\Models\NPArea;
use App\Models\NPCity;
use App\Models\NPWarehouse;
use Illuminate\Http\Request;

class NPController extends Controller
{
    public function areas(Request $request)
    {
        $search = $this->validateSearch($request);

        $areas = NPArea::query()
            ->when($search, fn ($q) =>
                $q->where('name', 'like', "%{$search}%")
            )
            ->orderBy('name')
            ->paginate(30);

        return response()->json([
            'data' => [
                'areas' => NPAreaResource::collection($areas->items()),
                'pagination' => new PaginationResource($areas),
            ],
        ]);
    }

    public function citiesByArea(string $areaRef, Request $request)
    {
        $search = $this->validateSearch($request);

        $cities = NPCity::query()
            ->where('area_ref', $areaRef)
            ->where('is_active', true)
            ->when($search, fn ($q) =>
                $q->where('name', 'like', "%{$search}%")
            )
            ->orderBy('name')
            ->paginate(30);

        return response()->json([
            'data' => [
                'cities' => NPCityResource::collection($cities->items()),
                'pagination' => new PaginationResource($cities),
            ],
        ]);
    }

    public function warehousesByCity(string $cityRef, Request $request)
    {
        $search = $this->validateSearch($request);

        $warehouses = NPWarehouse::query()
            ->where('city_ref', $cityRef)
            ->where('is_active', true)
            ->when($search, fn ($q) =>
                $q->where('name', 'like', "%{$search}%")
            )
            ->orderBy('name')
            ->paginate(30);

        return response()->json([
            'data' => [
                'warehouses' => NPWarehouseResource::collection($warehouses->items()),
                'pagination' => new PaginationResource($warehouses),
            ],
        ]);
    }

    private function validateSearch(Request $request): ?string
    {
        $search = $request->input('search');

        if (!$search) {
            return null;
        }

        $search = trim($search);

        if (mb_strlen($search) < 2) {
            return null;
        }

        if (mb_strlen($search) > 100) {
            return null;
        }

        return $search;
    }
}
