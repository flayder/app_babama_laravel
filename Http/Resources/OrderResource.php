<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class OrderResource extends JsonResource
{
    public function toArray($request): array|JsonSerializable|Arrayable
    {
        return $this->resource ? [
            'id' => $this->resource->id,
            'createdAt' => $this->resource->created_at,
            'price' => $this->resource->price ?? 0,
            'progress' => !is_null($this->remains) ? $this->quantity - $this->remains : 0,
            'progressMax' => $this->quantity,
            'isPaid' => $this->status !== 'unpaid',
            'activity' => $this->service?->activity?->id,
            'service' => $this->service?->id,
            'category' => $this->service?->category_id,
            'platform' => $this->service?->service_title,
            'text' => $this->service?->description,
            'link' => $this->link,
            'status' => $this->resource->status
        ] : [];
    }
}
