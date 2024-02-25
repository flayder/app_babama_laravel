<?php

namespace App\Services;

use App\Repositories\TransactionRepository;
use App\Http\Resources\ReferResource;
use Illuminate\Database\Eloquent\Collection;

use App\Models\Refer;
use App\Models\Order;


class ReferService
{
    private $refer;
    private $transactionRepository;
    private $payments;


    public function __construct(Refer|ReferResource $model)
    {
        $this->refer = $model;
        $this->transactionRepository = new TransactionRepository;
    }

    public function getReferralPaymentPercent(int $level)
    {
        return match($level) {
            1 => 15,
            2 => 20,
            3 => 25,
            4 => 30
        };
    }

    public function getReferralLevel(int|float|double $total)
    {
        $level = 1;
        if($total > 5000 && $total < 15000) $level = 2;
        elseif($total > 15000 && $total < 50000) $level = 3;
        elseif($total > 50000) $level = 4;

        return $level;
    }

    public function getBalanced()
    {
        $users = $this->refer->users;
        if($users) {
            return $this->transactionRepository->getBalanced($users)->count();
        }

        return 0;
    }

    public function calculateConversion(int $visits, int $payments)
    {
        $result = 0;

        if($visits > 0 && $payments > 0)
            $result = $visits / $payments * 100;

        return ($result > 0) ? floatval(number_format($result, 1, '.')) : 0;
    }

    public function calculateUserLevel(Collection|null $payments)
    {
        $total = $this->calculateReferralTotalOrderPrice($payments);
        return $this->getReferralLevel($total);
    }

    public function calculateReferralTotalOrderPrice(Collection|null $payments)
    {
        $total = 0;

        if($payments) {
            foreach ($payments as $payment) {
                $total += $payment->amount;
            }
        }

        return $total;
    }

    public function calculateReferralTotalEarn(Collection|null $payments)
    {
        $total = 0;

        if($payments) {
            foreach ($payments as $payment) {
                $total += $payment->total_amount;
            }
        }

        return $total;
    }

    public function calculateReferralCashFlow(Collection|null $payments)
    {
        $total = $this->calculateReferralTotalOrderPrice($payments);

        $cashflow = ($total > 0) ? $total : 0;

        return $cashflow > 0 ? $cashflow : 0;
    }
    
}
