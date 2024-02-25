<?php

declare(strict_types=1);

namespace App\Services\Gateway\stripe;

use Facades\App\Services\BasicService;
use StripeJS\Charge;
use StripeJS\Customer;
use StripeJS\StripeJS;

require_once 'stripe-php/init.php';

class Payment
{
    public static function prepareData($order, $gateway)
    {
        $basic = (object) config('basic');
        $val['key'] = $gateway->parameters->publishable_key ?? '';
        $val['name'] = optional($order->user)->username ?? $basic->site_title;
        $val['description'] = 'Payment with Stripe';
        $val['amount'] = ($order->final_amount * 100);
        $val['currency'] = $order->gateway_currency;
        $send['val'] = $val;
        $send['src'] = 'https://checkout.stripe.com/checkout.js';
        $send['view'] = 'user.payment.stripe';
        $send['method'] = 'post';
        $send['url'] = route('ipn', [$gateway->code, $order->transaction]);

        return json_encode($send);
    }

    public static function ipn($request, $gateway, $order = null): array
    {
        StripeJS::setApiKey($gateway->parameters->secret_key);

        $customer = Customer::create([
            'email' => $request->stripeEmail,
            'source' => $request->stripeToken,
        ]);

        $charge = Charge::create([
            'customer' => $customer->id,
            'description' => 'Payment with Stripe',
            'amount' => $order->amount * 100,
            'currency' => $gateway->currency,
        ]);

        if ('succeeded' == $charge['status']) {
            BasicService::preparePaymentUpgradation($order);
            $data['status'] = 'success';
            $data['msg'] = 'Transaction was successful.';
            $data['redirect'] = route('success');
        } else {
            $data['status'] = 'error';
            $data['msg'] = 'Unsuccessful transaction.';
            $data['redirect'] = route('failed');
        }

        return $data;
    }
}
