<?php

namespace App\Modules\Transaction\Services;

use App\Modules\Transaction\DTO\TransactionCreateDto;
use App\Modules\Transaction\Models\Transaction;
use Illuminate\Support\Str;

class TransactionService
{
    public static function create(TransactionCreateDto $createDto): Transaction
    {
        $transaction = new Transaction();

        $transaction->uuid = Str::uuid();
        $transaction->user_id = $createDto->user->id;
        $transaction->order_id = !empty($createDto->orderDto) ? $createDto->orderDto->id : null;
        $transaction->amount = $createDto->amount ?: null;
        $transaction->type = $createDto->type->value;
        $transaction->status = $createDto->status->value;
        $transaction->detail = !empty($createDto->detail) ? $createDto->detail : null;

        $transaction->save();

        return $transaction;
    }
}
