<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Services\ReferService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReferResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     */
    public function toArray($request): array
    {
        return [
            'id'            => $this->id,
            'link'          => env('APP_URL_REFERRAL') . $this->link,
            'visits'        => isset($this->visits) ? $this->visits : 0,
            'balanced'      => isset($this->balanced) ? $this->balanced : 0,
            'conversion'    => isset($this->conversion) ? $this->conversion : 0,
            'earn'          => isset($this->earn) ? $this->earn : 0,
            'deleted'       => (bool)$this->deleted
        ];
    }
}
