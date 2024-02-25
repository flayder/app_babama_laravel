<?php

declare(strict_types=1);

namespace App\Services;

use App\Http\Traits\Notify;
use App\Models\Transaction;
use Image;

class BasicService
{
    use Notify;

    public function validateImage(object $getImage, string $path)
    {
        if ('jpg' == $getImage->getClientOriginalExtension() || 'jpeg' == $getImage->getClientOriginalName() || 'png' == $getImage->getClientOriginalName()) {
            $image = uniqid().'.'.$getImage->getClientOriginalExtension();
        } else {
            $image = uniqid().'.jpg';
        }
        Image::make($getImage->getRealPath())->resize(300, 250)->save($path.$image);

        return $image;
    }

    public function validateDate(string $date)
    {
        if (preg_match("/^[0-9]{1,2}\/[0-9]{1,2}\/[0-9]{2,4}$/", $date)) {
            return true;
        } else {
            return false;
        }
    }

    public static function validateKeyword(string $search, string $keyword)
    {
        return preg_match('~'.preg_quote($search, '~').'~i', $keyword);
    }

    public function cryptoQR($wallet, $amount, $crypto = null)
    {
        $varb = $wallet.'?amount='.$amount;

        return "https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=$varb&choe=UTF-8";
    }

    public function preparePaymentUpgradation($order): void
    {
        if (0 == $order->status || 2 == $order->status) {
            $order['status'] = 1;
            $order->update();

            $user = $order->user;
            $user->balance += $order->amount;
            $user->save();

            $basic = (object) config('basic');

            $gateway = $order->gateway;
            $transaction = new Transaction();
            $transaction->user_id = $order->user_id;
            $transaction->trx_type = '+';
            $transaction->amount = $order->amount;
            $transaction->remarks = 'Deposit Via '.$gateway->name;
            $transaction->trx_id = $order->transaction;
            $transaction->charge = getAmount($order->charge);
            $transaction->save();

            if (1 == $basic->deposit_commission) {
                $this->setBonus($user, getAmount($order->amount), $type = 'deposit');
            }

            $msg = [
                'username' => $user->username,
                'amount' => getAmount($order->amount),
                'currency' => $basic->currency,
                'gateway' => $gateway->name,
            ];
            $action = [
                'link' => route('admin.user.fundLog', $user->id),
                'icon' => 'fa fa-money-bill-alt text-white',
            ];
            $this->adminPushNotification('PAYMENT_COMPLETE', $msg, $action);

            $this->sendMailSms($user, 'PAYMENT_COMPLETE', [
                'gateway_name' => $gateway->name,
                'amount' => getAmount($order->amount),
                'charge' => getAmount($order->charge),
                'currency' => $basic->currency,
                'transaction' => $order->transaction,
                'remaining_balance' => getAmount($user->balance),
            ]);
        }
    }

    public function setBonus($user, $amount, $commissionType = '')
    {
        $basic = (object) config('basic');
        $userId = $user->id;
        $i = 1;
        $level = \App\Models\Referral::where('commission_type', $commissionType)->count();
        while ('' != $userId || '0' != $userId || $i < $level) {
            $me = \App\Models\User::with('referral')->find($userId);

            $refer = $me->referral;
            if (!$refer) {
                break;
            }
            $commission = \App\Models\Referral::where('commission_type', $commissionType)->where('level', $i)->first();
            if (!$commission) {
                break;
            }
            $com = ($amount * $commission->percent) / 100;
            $new_bal = getAmount($refer->balance + $com);
            $refer->balance = $new_bal;
            $refer->save();

            $trx = strRandom();
            $remarks = ' level '.$i.' Referral bonus From '.$user->username;

            $transaction = new Transaction();
            $transaction->user_id = $refer->id;
            $transaction->trx_type = '+';
            $transaction->amount = $com;
            $transaction->remarks = $remarks;
            $transaction->trx_id = $trx;
            $transaction->charge = 0;
            $transaction->save();

            $bonus = new \App\Models\ReferralBonus();
            $bonus->from_user_id = $refer->id;
            $bonus->to_user_id = $user->id;
            $bonus->level = $i;
            $bonus->amount = getAmount($com);
            $bonus->main_balance = $new_bal;
            $bonus->transaction = $trx;
            $bonus->type = $commissionType;
            $bonus->remarks = $remarks;
            $bonus->save();

            $this->sendMailSms($refer, $type = 'REFERRAL_BONUS', [
                'transaction_id' => $trx,
                'amount' => getAmount($com),
                'currency' => $basic->currency_symbol,
                'bonus_from' => $user->username,
                'final_balance' => $refer->balance,
                'level' => $i,
            ]);

            $msg = [
                'bonus_from' => $user->username,
                'amount' => getAmount($com),
                'currency' => $basic->currency_symbol,
                'level' => $i,
            ];
            $action = [
                'link' => route('user.referral.bonus'),
                'icon' => 'fa fa-money-bill-alt',
            ];
            $this->userPushNotification($refer, 'REFERRAL_BONUS', $msg, $action);

            $userId = $refer->id;
            ++$i;
        }

        return 0;
    }
}
