<?php

namespace App\Jobs;

use App\Enums\BalanceMethodsEnum;
use App\Enums\OrderStatusEnum;
use App\Helper\SellerHelper;
use App\Models\ApiProvider;
use App\Models\User;
use App\Models\FreewalletPayment;
use App\Modules\Transaction\Models\Transaction;
use App\Services\FreewalletService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Http\Request;

class FreewalletPaymentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected FreewalletPayment $freewalletPayment;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(FreewalletPayment $freewalletPayment)
    {
        $this->freewalletPayment = $freewalletPayment;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $user = $this->freewalletPayment->user;

        if($user->referral_balance >= $this->freewalletPayment->amount) {
            $freewalletService = new FreewalletService;

            $freewalletService->confirm($this->freewalletPayment->id);
        } else {
            $this->freewalletPayment->description = "Оплата не была произведена из-за недостатка баланса на счету пользователя";
            $this->freewalletPayment->save();
        }

        // Log::info('[Referral refferral balnce fail] price is less than 2 rub]', [
        //     $this->freewalletPayment->id
        // ]);
    }
}
