<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\TaxonomyCollectionResource;
use App\Models\Category;

class CategoryController extends Controller
{
    public function categories()
    {
        $categories = Category::all();

        return response()->json([
            'data' => TaxonomyCollectionResource::collection($categories),
        ]);
    }
}
