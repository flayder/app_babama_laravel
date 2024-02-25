<?php

namespace App\Http\Controllers\Api\FreeKassa;

use App\Jobs\ReferralAddToBalanceJob;
use App\Jobs\PayOrderJob;
use App\Models\Transaction;
use App\Models\User;
use App\Repositories\FreewalletPaymentRepository;
use App\Modules\Transaction\DTO\TransactionDto;
use App\Modules\Transaction\Enums\TransactionStatusEnum;
use App\Modules\Transaction\Repositories\TransactionRepository;
use App\Services\Balance;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class WebhookController
{
    public function handleResult(Request $request): string
    {
        /**
       array (
        'MERCHANT_ID' => '44928',
        'AMOUNT' => '10',
        'intid' => '0',
        'MERCHANT_ORDER_ID' => '14117503-1879-4fdc-b9e9-b1c90cbe23d7',
        'P_EMAIL' => 'autorun71@mail.ru',
        'P_PHONE' => NULL,
        'CUR_ID' => '1',
        'commission' => '0.5',
        'SIGN' => 'e9225e5debc9f76b68bd21a1b963e677',
        )
         */
        \Log::info([
            'FreeKassa_data' => $request->all()
        ]);

        if (empty($request->input('SIGN'))) {
            exit(200);
        }

//        if (!in_array($_SERVER['REMOTE_ADDR'], array('185.71.65.92', '185.71.65.189', '149.202.17.210'))) return '';

        $amount = (double)$request->AMOUNT;

        $fee = (double)$request?->commission;

        $transactionUuid = $request->MERCHANT_ORDER_ID;
        $transaction = (new TransactionRepository())->getByUuid($transactionUuid);
        $transactionDto = new TransactionDto($transaction);
        \App\Modules\Transaction\Facades\Transaction::setSuccess($transactionDto);
        $invId = $request->MERCHANT_ID;

        $user = $transactionDto->user;

        if ($transactionDto->status->value != TransactionStatusEnum::PENDING->value) {
            return "OK$invId\n";
        }

        $balance = new Balance($user);
        $balance->add($amount, $transactionDto);
        dispatch(new ReferralAddToBalanceJob($amount, $user, $transaction));

        if (!empty($transactionDto->order)) {
            $orderService = OrderService::build($transactionDto->order);
            dispatch(new PayOrderJob($orderService, $user));
        }

        return "OK$invId\n";
    }

    public function handleWalletResult(Request $request)
    {
        $requests = $request->all();

        Log::info('[Freewallet payment]', $requests);
        $payment_id = (int)$requests['order_id'];
        $paymentRepository = new FreewalletPaymentRepository;
        $payment = $paymentRepository->getById($payment_id);

        if($payment) {
            if($requests['status'] == 1) {
                $referral_balance = $payment->user->referral_balance - $payment->amount;
                $payment->user->referral_balance = $referral_balance;
                $payment->user->save();

                $payment->description = "Успешный перевод";
                $payment->status = 1;
                $payment->save();
            } else {
                $payment->description = json_encode($requests);
                $payment->status = $requests['status'];
                $payment->save();
            }
        }
    }
}
