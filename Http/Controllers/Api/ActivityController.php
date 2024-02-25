<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ActivityResource;
use App\Models\Activity;
use App\Models\Category;

class ActivityController extends Controller
{
    /** @OA\Get (
     *     path="/api/category/{category_id}/activities",
     *     summary="Updates a user",
     *     @OA\Parameter(in="path", name="id", required=true, @OA\Schema(type="integer"),
     *      @OA\Examples(example="int", value="1", summary="An int value."),
     *     ),
     *     @OA\Response( response=200, description="OK")
     * ) */
    public function index(int|string $categoryId): array
    {
        $activities = Activity::where('category_id', $categoryId)->where('status', true)->get();

        return ActivityResource::collection($activities)->resolve();
    }

    public function show($category, $activity)
    {
        $category = Category::where('slug', $category)->first();

        $activity = Activity::where('category_id', $category->id)->where('slug', $activity)->first();

        if (!$activity) {
            abort(404);
        }

        return response()->json(new ActivityResource($activity));
    }
}
