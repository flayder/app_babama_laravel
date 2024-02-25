<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\StaticPageResource;
use App\Models\StaticPage;

class StaticPageController extends Controller
{
    public function index()
    {
        $pages = StaticPage::select('id','title','slug')->get();

        return StaticPageResource::collection($pages);
    }

    public function show(string $slug)
    {
        $page = StaticPage::where('slug',$slug)->firstOrFail();

        return StaticPageResource::make($page);
    }
}
