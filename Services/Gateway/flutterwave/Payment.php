<?php

declare(strict_types=1);

namespace App\Services\Gateway\flutterwave;

use Facades\App\Services\BasicCurl;
use Facades\App\Services\BasicService;

class Payment
{
    public static function prepareData($order, $gateway)
    {
        $send['API_publicKey'] = $gateway->parameters->public_key ?? '';
        $send['customer_email'] = optional($order->user)->email;
        $send['amount'] = $order->final_amount;
        $send['customer_phone'] = optional($order->user)->phone ?? '';
        $send['currency'] = $order->gateway_currency;
        $send['txref'] = $order->transaction;
        $send['view'] = 'user.payment.flutterwave';

        return json_encode($send);
    }

    public static function ipn($request, $gateway, $order = null, $trx = null, $type = null)
    {
        if ('error' == $type) {
            $data['status'] = 'error';
            $data['msg'] = 'transaction Failed.';
            $data['redirect'] = route('failed');
        } else {
            $url = 'https://api.ravepay.co/flwv3-pug/getpaidx/api/v2/verify';
            $headers = ['Content-Type:application/json'];
            $postParam = [
                'SECKEY' => $gateway->parameters->secret_key ?? '',
                'txref' => $order->transaction,
            ];

            $response = BasicCurl::curlPostRequestWithHeaders($url, $headers, $postParam);
            $response = json_decode($response);
            if ('successful' == $response->data->status && '00' == $response->data->chargecode && $order->final_amount == $response->data->amount && $order->gateway_currency == $response->data->currency && 0 == $order->status) {
                BasicService::preparePaymentUpgradation($order);

                $data['status'] = 'success';
                $data['msg'] = 'Transaction was successful.';
                $data['redirect'] = route('success');
            } else {
                $data['status'] = 'error';
                $data['msg'] = 'unable to Process.';
                $data['redirect'] = route('failed');
            }
        }

        return $data;
    }
}
