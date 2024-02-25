<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class FreewalletPaymentCurrencyResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' 						=> $this->freewallet_currency_id,
            'name' 						=> $this->name
        ];
    }
}
