<?php

namespace App\Jobs;

use App\Enums\BalanceMethodsEnum;
use App\Enums\OrderStatusEnum;
use App\Helper\SellerHelper;
use App\Models\ApiProvider;
use App\Models\User;
use App\Models\Refer;
use App\Modules\Transaction\Models\Transaction;
use App\Models\ReferralBalanceInfo;
use App\Repositories\ReferralBalanceInfoRepository;
use App\Repositories\ReferRepository;
use App\Services\ApiProviderService;
use App\Services\ReferService;
use App\Services\UserService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ReferralAddToBalanceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Transaction $transaction;

    protected User $user;
    protected int $amount;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int|float|double $amount, User $user, Transaction $transaction)
    {
        $this->amount = $amount;
        $this->transaction = $transaction;
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $referralsCollection = $this->user->refers;

        if(count($referralsCollection) > 0 && $this->amount > 2) {
            $referral = $referralsCollection[0]->user;

            $referService = new ReferService(new Refer);
            $transactionRepository = new ReferralBalanceInfoRepository;

            $referRepository = new ReferRepository;
            $referrals = $referRepository->getReferrals($referral);

            if(count($referrals) > 0) {
                $monthTotalOrders = $transactionRepository->getTransactionsByPeriod($referrals, now()->subMonths(1));

                $level = $referService->calculateUserLevel($monthTotalOrders);
                $paymentPercent = $referService->getReferralPaymentPercent($level);

                $userService = new UserService($referral);
                $total_amount = $userService->addReferralBalance($this->amount, $paymentPercent);

                $referralBalance = new ReferralBalanceInfo;

                $referralBalance->referral_id = $referral->id;
                $referralBalance->user_id = $this->user->id;
                $referralBalance->transaction_id = $this->transaction->id;
                $referralBalance->percent = $paymentPercent;
                $referralBalance->amount = $this->amount;
                $referralBalance->total_amount = $total_amount;
                $referralBalance->save();
            }
        } else {
            Log::info('[Referral refferral balnce fail] price is less than 2 rub]', [
                $this->transaction->id
            ]);
        }
    }
}
