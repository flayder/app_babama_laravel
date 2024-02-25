<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class FreewalletPaymentResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'account' => $this->acoount,
            'amount' => $this->amount,
            'description' => $this->description,
            'status' => $this->status,
            'payment_system'   => FreewalletPaymentSystemResource::make($this->payment_system),
            'currency'   => FreewalletPaymentCurrencyResource::make($this->currency),
            //'user' => UserResource::make($this->user),
            'created_at' => $this->created_at
        ];
    }
}
