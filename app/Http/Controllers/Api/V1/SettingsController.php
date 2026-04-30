<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\SettingResource;
use App\Models\Setting;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = Setting::all();

        return response()->json([
            'data' => SettingResource::collection($settings),
        ]);
    }

    public function show(string $key)
    {
        $setting = Setting::where('key', $key)->firstOrFail();

        return response()->json([
            'data' => new SettingResource($setting),
        ]);
    }
}
