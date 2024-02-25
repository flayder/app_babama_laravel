<?php

namespace App\Helper;

class FreeKassaHelper
{
    public static function getSignature(array $data): string
    {
        $apiKey = config('app.freekassa.api_key');
        $newData = [
            'shopId' => $data['shopId'],
            'nonce' => $data['nonce']
        ];
        ksort($newData);
//        return hash_hmac('sha256', implode('|', $newData), $apiKey);

        $merchant_id = '44928';
        $secret_word = 'secret';
        $order_id = '154';
        $order_amount = '100.11';
        $currency = 'RUB';
        $sign = md5($merchant_id.':'.$order_amount.':'.$secret_word.':'.$currency.':'.$order_id);
    }
}
