<?php

declare(strict_types=1);

namespace App\Services\Gateway\blockio;

use App\Order;
use Facades\App\Services\BasicService;

class Payment
{
    public static function prepareData($order, $gateway)
    {
        $apiKey = $gateway->parameters->api_key ?? '';
        $apiPin = $gateway->parameters->api_pin ?? '';
        $version = 2;
        $blockIo = new BlockIo($apiKey, $apiPin, $version);

        if (0 == $order->btc_amount || '' == $order->btc_wallet) {
            $btcdata = $blockIo->get_current_price(['price_base' => 'USD']);
            if ('success' != $btcdata->status) {
                $send['error'] = true;
                $send['message'] = 'Unable to Process';
            }
            $btcrate = $btcdata->data->prices[0]->price;
            $usd = $order->final_amount;
            $btc = round($usd / $btcrate, 8);

            $address = $blockIo->get_new_address();

            if ('success' == $address->status) {
                $blockIoAdress = $address->data;
                $wallet = $blockIoAdress->address;
                $order['btc_wallet'] = $wallet;
                $order['btc_amount'] = $btc;
                $order->update();
            } else {
                $send['error'] = true;
                $send['message'] = 'Unable to Process';
            }
        }
        $send['amount'] = $order->btc_amount;
        $send['sendto'] = $order->btc_wallet;
        $send['img'] = BasicService::cryptoQR($order->btc_wallet, $order->btc_amount);
        $send['currency'] = $order->gateway_currency ?? 'BTC';

        $send['view'] = 'user.payment.crypto';

        return json_encode($send);
    }

    public static function ipn($request, $gateway, $order = null, $trx = null, $type = null): void
    {
        $apiKey = $gateway->parameters->api_key ?? '';
        $apiPin = $gateway->parameters->api_pin ?? '';
        $version = 2;
        $block_io = new BlockIo($apiKey, $apiPin, $version);
        $orderData = Order::with('gateway')
            ->whereHas('gateway', function ($query): void {
                $query->where('code', 'blockio');
            })
            ->where('status', 0)
            // ->where('btc_amount', '>', 0)
            ->where('btc_wallet', '')
            ->latest()
            ->get();
        foreach ($orderData as $data) {
            $balance = $block_io->get_address_balance(['addresses' => $data->btc_wallet]);
            if (@$balance->data->available_balance >= $data->btc_amount && '0' == $data->status) {
                BasicService::preparePaymentUpgradation($order);
            }
        }
    }
}
