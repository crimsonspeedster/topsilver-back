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
use Illuminate\Support\Facades\Http;

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
            ->get();

        return response()->json([
            'data' => NPAreaResource::collection($areas)
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

    public function streetsByCity(string $localityRef, Request $request)
    {
        $search = $this->validateSearch($request);

        if (!$search) {
            return response()->json([
                'message' => 'Введіть 3 або більше символів для пошуку',
            ], 422);
        }

        $response = Http::post('https://api.novaposhta.ua/v2.0/json/', [
            'apiKey' => config('services.nova_poshta_key'),
            'modelName' => 'Address',
            'calledMethod' => 'searchSettlementStreets',
            'methodProperties' => [
                'SettlementRef' => $localityRef,
                'StreetName' => $search,
                'Limit' => 30,
            ],
        ]);

        return response()->json(
            $response->json(),
            200,
            [],
            JSON_UNESCAPED_UNICODE
        );
    }

    public function localities(Request $request)
    {
        $search = $this->validateSearch($request);

        if (!$search) {
            return response()->json([
                'message' => 'Введіть 3 або більше символів для пошуку',
            ], 422, [], JSON_UNESCAPED_UNICODE);
        }

        $response = Http::post('https://api.novaposhta.ua/v2.0/json/', [
            'apiKey' => config('services.nova_poshta_key'),
            'modelName' => 'Address',
            'calledMethod' => 'getSettlements',
            'methodProperties' => [
                'FindByString' => $search,
                'Limit' => 30,
            ],
        ]);

        return response()->json(
            $response->json(),
            200,
            [],
            JSON_UNESCAPED_UNICODE
        );
    }

    private function validateSearch(Request $request): ?string
    {
        $search = $request->input('search');

        if (!$search) {
            return null;
        }

        $search = trim($search);

        if (mb_strlen($search) < 3) {
            return null;
        }

        if (mb_strlen($search) > 100) {
            return null;
        }

        return $search;
    }
}
