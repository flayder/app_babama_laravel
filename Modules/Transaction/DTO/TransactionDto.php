<?php

namespace App\Modules\Transaction\DTO;

use App\Dto\Order\OrderDto;
use App\Models\User;
use App\Modules\Transaction\Enums\TransactionStatusEnum;
use App\Modules\Transaction\Enums\TransactionTypeEnum;
use App\Modules\Transaction\Models\Transaction;
use Carbon\Carbon;

class TransactionDto
{
    public int $id;

    public string $uuid;

    public User $user;

    public ?OrderDto $order = null;

    public ?float $amount = 0.0;

    public TransactionTypeEnum $type;

    public TransactionStatusEnum $status;

    public ?string $detail = '';

    public Carbon $createdAt;

    public function __construct(Transaction $transaction)
    {
        $this->id = $transaction->id;
        $this->uuid = $transaction->uuid;
        $this->user = $transaction->user;
        $this->type = TransactionTypeEnum::tryFrom($transaction->type);
        $this->status = TransactionStatusEnum::tryFrom($transaction->status);
        $this->detail = $transaction->detail;
        $this->amount = $transaction->amount != null ? $transaction->amount : 0.0;
        $this->createdAt = new Carbon($transaction->created_at);

        if (!empty($transaction->order)) {
            $this->order = new OrderDto($transaction->order);
        }


    }
}
