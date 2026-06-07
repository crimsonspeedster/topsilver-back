<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ContentEntityResource;
use App\Http\Resources\SeoPageResource;
use App\Models\Page;

class PageController extends Controller
{
    public function home()
    {
        $home_page_relation = settings('home_page');

        if (!$home_page_relation) {
            abort(404);
        }

        $page_id = (int) $home_page_relation;
        $page = Page::findOrFail($page_id);

        return response()->json([
            'data' => new ContentEntityResource($page),
        ]);
    }

    public function home_seo()
    {
        $home_page_relation = settings('home_page');

        if (!$home_page_relation) {
            abort(404);
        }

        $page_id = (int) $home_page_relation;
        $page = Page::findOrFail($page_id)->load([
            'seo'
        ]);

        return response()->json([
            'data' => new SeoPageResource($page),
        ]);
    }
}
