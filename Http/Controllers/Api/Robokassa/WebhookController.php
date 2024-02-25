<?php

namespace App\Http\Controllers\Api\Robokassa;

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
//array (
//'out_summ' => '10.000000',
//'OutSum' => '10.000000',
//'inv_id' => '52127282',
//'InvId' => '52127282',
//'crc' => '8BC5EE0F4ABE8F38C384700B7AEBD50A',
//'SignatureValue' => '8BC5EE0F4ABE8F38C384700B7AEBD50A',
//'PaymentMethod' => 'BankCard',
//'IncSum' => '10.000000',
//'IncCurrLabel' => 'YandexPayPSBR',
//'EMail' => 'e.tchemyakin@yandex.ru',
//'Fee' => '0.500000',
//'Shp_user' => '13',
//)

    public function handleResult(Request $request): string
    {

        \Log::info([
            'robokassa_data' => $request->all()
        ]);
        $amount = (double)$request->OutSum;

        $fee = (double)$request?->Fee;

        $transactionUuid = $request->Shp_user;
        $transaction = (new TransactionRepository())->getByUuid($transactionUuid);
        $transactionDto = new TransactionDto($transaction);
        \App\Modules\Transaction\Facades\Transaction::setSuccess($transactionDto);
        $invId = $request->InvId;

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
}
