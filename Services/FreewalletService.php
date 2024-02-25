<?php

namespace App\Services;

use App\Repositories\FreewalletPaymentRepository;
use App\Http\Resources\ReferResource;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Http;

use App\Models\FreewalletPayment;
use App\Models\Order;
use App\Models\User;


class FreewalletService
{
    private $freewalletPaymentRepository;
    private $payments;


    public function __construct()
    {
        $this->freewalletPaymentRepository = new FreewalletPaymentRepository;
    }

    private function validate(array $data) : bool
    {
        if(
            !isset($data['user_id']) ||
            !isset($data['payment_system_id']) ||
            !isset($data['currency_id']) ||
            !isset($data['amount']) ||
            !isset($data['account'])
        ) return false;

        return true;
    }

    public function create(array $data) : int
    {
        if($this->validate($data))
        {
            $payment = [];
            $payment['amount'] = $data['amount'];
            $payment['payment_system_id'] = $data['payment_system_id'];
            $payment['user_id'] = $data['user_id'];
            $payment['currency_id'] = $data['currency_id'];
            $payment['account'] = $data['account'];

            if(isset($data['description']))
                $payment['description'] = $data['description'];

            if(isset($data['status']))
                $payment['description'] = $data['status'];

            $payment = FreewalletPayment::create($payment);

            return $payment->id;
        }

        return 0;
    }

    public function updateStatus(int $freewalet_payment_id, $status) : void
    {
        $freewalletPayment = $this->freewalletPaymentRepository->getById($freewalet_payment_id);

        if($freewalletPayment) {
            $freewalletPayment->status = $status;
            $freewalletPayment->save();
        }
    }

    public function confirm(int $freewalet_payment_id, $fkwallet = false) : string
    {
        $public_key = env('FREEWALLET_PUBLIC_KEY');
        $private_key = env('FREEWALLET_PRIVATE_KEY');
        $freewalletPayment = $this->freewalletPaymentRepository->getById($freewalet_payment_id);

        if($freewalletPayment) {
            $data = [
                'amount'                => $freewalletPayment->amount,
                'currency_id'           => $freewalletPayment->currency->freewallet_currency_id,
                'fee_from_balance'      => 0,
                'idempotence_key'       => (string)$freewalletPayment->id
            ];

            if(!$fkwallet) {
                $data['payment_system_id'] = $freewalletPayment->payment_system->freewallet_payment_id;
                $data['account'] = $freewalletPayment->account;
                $data['order_id'] = $freewalletPayment->id;
            } else {
               $data['to_wallet_id'] = $freewalletPayment->account;
            }

            if($freewalletPayment->currency->freewallet_currency_id != 3) {
                $sign = hash('sha256', json_encode($data) . $private_key);

                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $sign
                ])->post('https://api.fkwallet.io/v1/'.$public_key.'/withdrawal', $data);

                $response = $response->json();

                if(isset($response['message']) && $response['message'])
                    return $response['message'];
            } else {
                if($freewalletPayment->user->referral_balance >= floatval($freewalletPayment->amount)) {
                    $freewalletPayment->user->referral_balance -= floatval($freewalletPayment->amount);
                    $freewalletPayment->user->balance += floatval($freewalletPayment->amount);
                    $freewalletPayment->user->save();

                    $freewalletPayment->status = "1";
                    $freewalletPayment->description = "Успешный перевод";
                    $freewalletPayment->save();
                } else {
                    return "У пользователя недостаточно средств на реферальном счету";
                }
            }
        }
        return "";
    }

    public function getTotalPrice(User $user, bool $isBalance = false)
    {
        $total = 0;

        $payments = $this->freewalletPaymentRepository->getTotalPrice($user, $isBalance);

        foreach ($payments as $payment) {
            $total += $payment->amount;
        }

        return $total;
    }
    
}
