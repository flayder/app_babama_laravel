<?php

namespace App\Services;

use App\Models\User;

class BalanceService
{
    private User $user;
    
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function get(): float
    {
        return $this->user->balance;
    }

    public function change(float $balance): void
    {
        $this->user->balance = $balance;
        $this->user->saveOrFail();
    }

    public function add(float $sum): void
    {
        $this->user->balance += $sum;
        $this->user->saveOrFail();
    }

    public function deduct(float $sum): void
    {
        $this->user->balance -= $sum;
        $this->user->saveOrFail();
    }

    public function reset(): void
    {
        $this->user->balance = 0.00;
        $this->user->saveOrFail();
    }
}
