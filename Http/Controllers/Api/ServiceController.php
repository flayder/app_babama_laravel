<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ServiceResource;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    /** @OA\Get (
     *     path="/api/category/{category_id}/activity/{activity_id}/services",
     *     @OA\Parameter(in="path", name="id", required=true, @OA\Schema(type="integer"),
     *      @OA\Examples(example="integer", value="1", summary="An int value."),
     *     ),
     *     @OA\Response( response=200, description="OK")
     * ) */
    public function index(string|int $categoryId, string|int $activityId): array
    {
        $services = Service::with('parameters.country')
            ->with('activity')
            ->where('category_id', $categoryId)
            ->where('activity_id', $activityId)
            ->where('service_status', 1)
            ->orderBy('priority')
            ->get();

        foreach ($services as $service) {
            if (empty($service->parameters) || $service->parameters->count() < 1) {
                continue;
            }
            $parameters = $service->parameters->reverse();
            $parameters->push((object)[
                "id" => null,
                "service" => null,
                "country" => (object)[
                    "name" => "Любая",
                    "id" => null,
                ],
                "any" => null,
                "male" => null,
                "female" => null,
            ]);
            $service->parameters = $parameters->reverse();

        }

        return ServiceResource::collection($services)->resolve();
    }

    public function all(Request $request): array
    {
        $key = $request->input('key');
        if (empty($key) || $key != 'searchADM') {
            abort(404);
        }
        $find = $request->input('find');
        $services = Service::with('parameters.country')
            ->with('activity')
            ->where('service_title', 'like', "%{$find}%")
            ->orderBy('id')
            ->get();

        return ServiceResource::collection($services)->resolve();
    }
}
