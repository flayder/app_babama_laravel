<?php

namespace App\Http\Controllers\Api\Payeer;

use App\Jobs\ReferralAddToBalanceJob;
use App\Jobs\PayOrderJob;
use App\Models\Transaction;
use App\Models\User;
use App\Modules\Transaction\DTO\TransactionDto;
use App\Modules\Transaction\Enums\TransactionStatusEnum;
use App\Modules\Transaction\Repositories\TransactionRepository;
use App\Services\Balance;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController
{
    public function handleResult(Request $request): string
    {
        \Log::info([
            'payeer_data' => $request->all()
        ]);
//        if (!in_array($_SERVER['REMOTE_ADDR'], array('185.71.65.92', '185.71.65.189', '149.202.17.210'))) return '';

        if (!empty($request->input('m_operation_id')) && !empty($request->input('m_sign')))
        {
            $m_key = config('app.payer.key');

            $arHash = array(
                $request->input('m_operation_id'),
                $request->input('m_operation_ps'),
                $request->input('m_operation_date'),
                $request->input('m_operation_pay_date'),
                $request->input('m_shop'),
                $request->input('m_orderid'),
                $request->input('m_amount'),
                $request->input('m_curr'),
                $request->input('m_desc'),
                $request->input('m_status')
            );
            $transactionUuid = $request->input('m_orderid');

            if (!empty($request->input('m_params')))
            {
                $arHash[] = $request->input('m_params');
            }

            $arHash[] = $m_key;

            $sign_hash = strtoupper(hash('sha256', implode(':', $arHash)));

            if ($request->input('m_sign') == $sign_hash && $request->input('m_status') == 'success')
            {
                $amount = $request->input('m_amount');

                $transaction = (new TransactionRepository())->getByUuid($transactionUuid);
                $transactionDto = new TransactionDto($transaction);
                \App\Modules\Transaction\Facades\Transaction::setSuccess($transactionDto);

                $user = $transactionDto->user;

                if ($transactionDto->status->value != TransactionStatusEnum::PENDING->value) {
                    ob_end_clean(); exit($request->input('m_orderid').'|success');
                }

                $balance = new Balance($user);
                $balance->add($amount, $transactionDto);
                dispatch(new ReferralAddToBalanceJob($amount, $user, $transaction));
                Log::info('[Payeer] transactionDto', [
                    $transactionDto
                ]);

                if (!empty($transactionDto->order)) {
                    $orderService = OrderService::build($transactionDto->order);
                    Log::info('[Payeer] orderService]', [
                        $orderService
                    ]);
                    dispatch(new PayOrderJob($orderService, $user));
                }

                ob_end_clean(); exit($request->input('m_orderid').'|success');
            }

            ob_end_clean(); exit($request->input('m_orderid').'|error');
        }


//
        return 'ok';
    }
}
