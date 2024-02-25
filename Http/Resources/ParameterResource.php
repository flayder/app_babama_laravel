<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ParameterResource extends JsonResource
{
    public function toArray($request): array
    {
        $genders = [];

        if (optional($this?->any)['service_price_diff']) {
            $genders[] = [
                'value' => 'any',
                'title' => 'Любой',
                'price_diff' => $this->any['service_price_diff']
            ];
        }

        if (optional($this?->female)['service_price_diff']) {
            $genders[] = [
                'value' => 'female',
                'title' => 'Женский',
                'price_diff' => $this->female['service_price_diff']
            ];
        }

        if (optional($this?->male)['service_price_diff']) {
            $genders[] = [
                'value' => 'male',
                'title' => 'Мужской',
                'price_diff' => $this->male['service_price_diff']
            ];
        }

        return [
            'title' => $this->country->name,
            'id' => (string)$this->country->id,
            'value' => (string)$this->country->id,
            'genders' => $genders
        ];
    }
}
