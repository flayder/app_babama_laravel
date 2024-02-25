<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Traits\Notify;
use App\Http\Traits\Upload;
use App\Models\Fund;
use App\Models\Gateway;
use Carbon\Carbon;
use Facades\App\Services\BasicService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    use Notify;
    use Upload;

    public function gatewayIpn(Request $request, $code, $trx = null, $type = null)
    {
        if (isset($request->m_orderid)) {
            $trx = $request->m_orderid;
        }

        if ('coinbasecommerce' == $code) {
            $input = fopen('php://input', 'r');
            @file_put_contents(time().'_coinbasecommerce.txt', $input);

            $gateway = Gateway::where('code', $code)->first();

            $postdata = file_get_contents('php://input');
            // $postdata = file_get_contents("1644427281_coinbasecommerce.txt");
            $res = json_decode($postdata);

            if (isset($res->event)) {
                $order = Fund::where('transaction', $res->event->data->metadata->trx)->orderBy('id', 'DESC')->with(['gateway', 'user'])->first();
                // $headers = apache_request_headers();
                // $sentSign = $headers['X-Cc-Webhook-Signature'];
                $sentSign = $request->header('X-Cc-Webhook-Signature');

                $sig = hash_hmac('sha256', $postdata, $gateway->parameters->secret);
                @file_put_contents(time().'_coinbasecommerce_sign.txt', $sentSign);

                if ($sentSign == $sig) {
                    if ('charge:confirmed' == $res->event->type && 0 == $order->status) {
                        BasicService::preparePaymentUpgradation($order);
                    }
                }
            }

            session()->flash('success', 'You request has been processing.');

            return redirect()->route('user.fund-history');
        }

        try {
            $gateway = Gateway::where('code', $code)->first();
            if (!$gateway) {
                throw new \Exception('Invalid Payment Gateway.');
            }
            if (isset($trx)) {
                $order = Fund::where('transaction', $trx)->orderBy('id', 'desc')->first();
                if (!$order) {
                    throw new \Exception('Invalid Payment Request.');
                }
            }
            $getwayObj = 'App\\Services\\Gateway\\'.$code.'\\Payment';
            $data = $getwayObj::ipn($request, $gateway, @$order, @$trx, @$type);
        } catch (\Exception $exception) {
            return back()->with('error', $exception->getMessage());
        }
        if (isset($data['redirect'])) {
            return redirect($data['redirect'])->with($data['status'], $data['msg']);
        }
    }

    public function success()
    {
        return view('success');
    }

    public function failed()
    {
        return view('failed');
    }

    /**
     * @param $user
     * @param $gate
     * @param $charge
     * @param $final_amo
     */
    public function newFund(Request $request, $user, $gate, $charge, $final_amo): Fund
    {
        $fund = new Fund();
        $fund->user_id = $user->id;
        $fund->gateway_id = $gate->id;
        $fund->gateway_currency = strtoupper($gate->currency);
        $fund->amount = $request->amount;
        $fund->charge = $charge;
        $fund->rate = $gate->convention_rate;
        $fund->final_amount = getAmount($final_amo);
        $fund->btc_amount = 0;
        $fund->btc_wallet = '';
        $fund->transaction = strRandom();
        $fund->try = 0;
        $fund->status = 0;
        $fund->save();

        return $fund;
    }
}
