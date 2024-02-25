<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class ReferralBalanceInfoResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'percent' => $this->percent,
            'amount' => $this->amount,
            'total_amount' => $this->total_amount,
            //'transaction' => $this->transaction,
            'user' => UserResource::make($this->user),
            'referral' => UserResource::make($this->referral),
            'created_at' => $this->created_at
        ];
    }
}
