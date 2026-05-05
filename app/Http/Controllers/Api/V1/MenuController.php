<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\MenuResource;
use App\Models\Location;
use App\Models\Menu;

class MenuController extends Controller
{
    public function index()
    {
        $menus = Menu::with([
            'items.children',
            'location',
        ])->get();

        return response()->json([
            'data' => MenuResource::collection($menus),
        ]);
    }

    public function show(Location $location)
    {
        $menu = Menu::where('location_id', $location->id)
            ->with(['items.children', 'location'])
            ->firstOrFail();

        return response()->json([
            'data' => new MenuResource($menu),
        ]);
    }
}
