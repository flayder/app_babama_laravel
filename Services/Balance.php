<?php

namespace App\Services;

use App\Enums\BalanceMethodsEnum;
use App\Jobs\BalanceJob;
use App\Models\User;
use App\Modules\Transaction\DTO\TransactionDto;

class Balance
{
    private User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function get(): float
    {
        return (float)$this->user->balance;
    }

    public function change(float $balance, ?TransactionDto $transactionDto = null): void
    {
        dispatch(new BalanceJob($this->user, BalanceMethodsEnum::UPDATE, $balance, $transactionDto));
    }

    public function add(float $sum, ?TransactionDto $transactionDto = null): void
    {
        dispatch(new BalanceJob($this->user, BalanceMethodsEnum::ADD, $sum, $transactionDto));
    }

    public function deduct(float $sum, ?TransactionDto $transactionDto = null): void
    {
        dispatch(new BalanceJob($this->user, BalanceMethodsEnum::DEDUCT, $sum, $transactionDto));
    }

    public function reset(?TransactionDto $transactionDto = null): void
    {
        dispatch(new BalanceJob($this->user, BalanceMethodsEnum::RESET, 0, $transactionDto));
    }
}
