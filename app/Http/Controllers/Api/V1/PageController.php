<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PageResource;
use App\Models\Page;

class PageController extends Controller
{
    public function show(Page $page)
    {
        $page->load([
            'seo'
        ]);

        return response()->json([
            'data' => new PageResource($page),
        ]);
    }
}
