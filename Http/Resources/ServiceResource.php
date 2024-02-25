<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class ServiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     *
     * @return array|Arrayable|JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->service_title,
            'link' => $this->link,
            'min_amount' => $this->min_amount,
            'max_amount' => $this->max_amount,
            'api_service_id' => $this->api_service_id,
            'price' => $this->price,
            'short_description' => $this->short_description ?? '',
            'link_demo' => $this->link_demo,
            'description' => $this->service_description ? $this->service_description : ($this->activity?->description ?? ''),
            'parameters' => ParameterResource::collection($this->parameters)->resolve()
        ];
    }
}
