<?php

namespace App\Services;

use App\Enums\BalanceMethodsEnum;
use App\Jobs\BalanceJob;
use App\Models\User;
use App\Modules\Transaction\DTO\TransactionDto;

class UserService
{
    private User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function addReferralBalance(int|float|double $amount, int $percent)
    {
        $total_amount = $amount * $percent / 100;

        if($total_amount > 0) {
            $this->user->referral_balance += $total_amount;
            $this->user->save();
        }

        return $total_amount;
    }
}
