<?php

declare(strict_types=1);

namespace App\Services\Gateway\paytm;

use Facades\App\Services\BasicService;

class Payment
{
    public static function prepareData($order, $gateway)
    {
        $val['MID'] = trim($gateway->parameters->MID);
        $val['WEBSITE'] = trim($gateway->parameters->WEBSITE);
        $val['CHANNEL_ID'] = trim($gateway->parameters->CHANNEL_ID);
        $val['INDUSTRY_TYPE_ID'] = trim($gateway->parameters->INDUSTRY_TYPE_ID);
        $val['ORDER_ID'] = $order->transaction;
        $val['TXN_AMOUNT'] = round($order->amount, 2);
        $val['CUST_ID'] = $order->user_id;
        $val['CALLBACK_URL'] = route('ipn', [$gateway->code, $order->transaction]);
        $val['CHECKSUMHASH'] = (new PayTM())->getChecksumFromArray($val, trim($gateway->parameters->merchant_key));
        $send['val'] = $val;
        $send['view'] = 'user.payment.redirect';
        $send['method'] = 'post';
        $send['url'] = trim($gateway->parameters->transaction_url).'?orderid='.$order->transaction;

        return json_encode($send);
    }

    public static function ipn($request, $gateway, $order = null, $trx = null, $type = null)
    {
        $ptm = new PayTM();
        if ('TRUE' === $ptm->verifychecksum_e($request, trim($gateway->parameters->merchant_key), $request->CHECKSUMHASH)) {
            if ('01' == $request->RESPCODE) {
                $requestParamList = ['MID' => trim($gateway->parameters->MID), 'ORDERID' => $request->ORDERID];
                $StatusCheckSum = $ptm->getChecksumFromArray($requestParamList, trim($gateway->parameters->merchant_key));
                $requestParamList['CHECKSUMHASH'] = $StatusCheckSum;
                $responseParamList = $ptm->callNewAPI(trim($gateway->parameters->transaction_status_url), $requestParamList);
                if ('TXN_SUCCESS' == $responseParamList['STATUS'] && $responseParamList['TXNAMOUNT'] == $request->TXNAMOUNT) {
                    BasicService::preparePaymentUpgradation($order);

                    $data['status'] = 'success';
                    $data['msg'] = 'Transaction was successful.';
                    $data['redirect'] = route('success');
                } else {
                    $data['status'] = 'error';
                    $data['msg'] = 'it seems some issue in server to server communication. Kindly connect with administrator';
                    $data['redirect'] = route('failed');
                }
            } else {
                $data['status'] = 'error';
                $data['msg'] = $request->RESPMSG;
                $data['redirect'] = route('failed');
            }
        } else {
            $data['status'] = 'error';
            $data['msg'] = 'security error!';
            $data['redirect'] = route('failed');
        }

        return $data;
    }
}
