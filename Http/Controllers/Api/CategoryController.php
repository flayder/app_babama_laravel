<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CategoryController extends Controller
{
    /**  @OA\Get (path="/api/categories",
     *     @OA\Response( response=200, description="OK")
     *   )
     */
    public function index(): AnonymousResourceCollection
    {
        $categories = Category::where('status', 1)->orderBy('created_at')->get();

        return CategoryResource::collection($categories);
    }

    /**  @OA\Get (path="/api/categories",
     *     @OA\Response( response=200, description="OK")
     *   )
     */
    public function show($category)
    {
        $category = Category::where('slug', $category)->first();

        if (!$category) {
            abort(404);
        }

        return response()->json(new CategoryResource($category));
    }
}
