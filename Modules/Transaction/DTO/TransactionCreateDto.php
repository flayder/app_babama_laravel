<?php

namespace App\Modules\Transaction\DTO;

use App\Dto\Order\OrderDto;
use App\Models\User;
use App\Modules\Transaction\Enums\TransactionStatusEnum;
use App\Modules\Transaction\Enums\TransactionTypeEnum;

class TransactionCreateDto
{
    public User $user;

    public ?OrderDto $orderDto;

    public float $amount = 0.0;

    public TransactionTypeEnum $type;

    public TransactionStatusEnum $status;

    public ?string $detail;

    public function __construct(User $user, $data, ?OrderDto $orderDto = null)
    {
        $this->user = $user;
        $this->type = TransactionTypeEnum::tryFrom($data['type']);
        $this->status = TransactionStatusEnum::tryFrom($data['status']);

        if (!empty($data['detail'])) {
            $this->detail = $data['detail'];
        }

        if (!empty($data['amount'])) {
            $this->amount = $data['amount'];
        }

        if (!empty($orderDto)) {
            $this->orderDto = $orderDto;
            if (empty($data['amount'])) {
                $this->amount = $orderDto->price;
            }
        }

    }
}
