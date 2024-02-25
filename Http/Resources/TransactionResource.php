<?php

namespace App\Http\Resources;

use App\Modules\Transaction\Enums\TransactionTypeEnum;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray($request): array
    {
        $remarks = $this->type->label() . (
            $this->type->value == TransactionTypeEnum::ORDER->value || $this->type->value == TransactionTypeEnum::PAY->value ? (' #' . $this->order?->id ): ''
            );

        if ($this->type->value == TransactionTypeEnum::ORDER_STATUS->value) {
            $remarks = $this->detail;
        }


        return [
            'id' => $this->id,
            'created_at' => $this->createdAt->format('Y-m-d h:i:s'),
            'amount' => $this->amount,
            'remarks' => $remarks,
            'status' => $this->status->label(),
            'trx_id' => $this->uuid,
            'trx_type' =>
                $this->type->value == TransactionTypeEnum::REPLENISHMENT->value
                    ? '+' : (
                    $this->type->value == TransactionTypeEnum::PAY->value ? '-' : ''
            ),
        ];
    }
}
