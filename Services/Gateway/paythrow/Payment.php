<?php

declare(strict_types=1);

namespace App\Services\Gateway\paythrow;

require 'php-sdk/vendor/autoload.php';

use App\Fund;
use Facades\App\Services\BasicService;
use PayThrow\Api\RedirectUrls;

class Payment
{
    public static function prepareData($order, $gateway): void
    {
        // Payer Object
        $payer = new \PayThrow\Api\Payer();
        $payer->setPaymentMethod('PayThrow'); // preferably, your system name, example - paythrow

        // Amount Object
        $amountIns = new \PayThrow\Api\Amount();
        $amountIns->setTotal(round($order->final_amount, 2))->setCurrency($order->gateway_currency); // must give a valid currency code and must exist in merchant wallet list

        // Transaction Object
        $trans = new \PayThrow\Api\Transaction();
        $trans->setAmount($amountIns);

        // RedirectUrls Object
        $urls = new RedirectUrls();
        $urls->setSuccessUrl(route(optional($order->gateway)->extra_parameters->ipn_url, optional($order->gateway)->code)) // success url - the merchant domain page,
        ->setCancelUrl(route('user.addFund')); // cancel url - the merchant domain page, to redirect after cancellation of payment

        // Payment Object
        $payment = new \PayThrow\Api\Payment();

        $payment->setCredentials([ // client id & client secret, see merchants->setting(gear icon)
            'client_id' => optional($order->gateway)->parameters->client_id,
            'client_secret' => optional($order->gateway)->parameters->client_secret,
        ])->setRedirectUrls($urls)
            ->setPayer($payer)
            ->setTransaction($trans);

        try {
            $payment->create(); // create payment
            header('Location: '.$payment->getApprovedUrl()); // checkout url
        } catch (\Exception $ex) {
            echo $ex;
            exit;
        }
        exit;
    }

    public static function ipn($request, $gateway, $order = null, $trx = null, $type = null): void
    {
        $encoded = json_encode($_GET);

        $decoded = json_decode(base64_decode($encoded), true);

        if (200 == $decoded['status']) {
            $order = Fund::where('transaction', $decoded['transaction_id'])->orderBy('id', 'DESC')->first();
            if ($order) {
                if ($decoded['currency'] == $order->gateway_currency && ($decoded['amount'] == round($order->final_amount, 2)) && 0 == $order->status) {
                    BasicService::preparePaymentUpgradation($order);
                }
            }
        }
    }
}
