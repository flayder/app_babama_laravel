<?php

namespace App\Http\Controllers\Api;

use App\Dto\Order\OrderDto;
use App\Dto\UserCreateDto;
use App\Helper\FreeKassaHelper;
use App\Http\Controllers\Controller;
use App\Modules\Transaction\DTO\TransactionDto;
use App\Modules\Transaction\Facades\Transaction;
use App\Repositories\OrderRepository;
use GuzzleHttp\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    public function getPaymentLink(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => 'required|string',
            'system' => 'required|string',
            'order_id' => 'nullable|int'
        ]);
        $orderDto = null;
        if (!empty($validated['order_id'])) {
            $order = (new OrderRepository())->getById($validated['order_id']);
            $orderDto = new OrderDto($order);
        }

        $transaction = Transaction::addReplenishment(\auth()->user(), $validated['amount'], $orderDto);

        $link = match($validated['system']) {
            'robokassa' => $this->generateRobokassaLink($transaction),
            'payeer' => $this->generatePayeerLink($transaction),
            'freekassa' => $this->generateFreeKassaLink($transaction),
        };



        return response()->json([
            'data' => $link
        ]);
    }

    public function getPaymentLinkEmail(Request $request): JsonResponse
    {
        $request->validate([
            'amount' => 'required|string',
            'system' => 'required|string',
            'email' => 'required|string'
        ]);

        $userDto = new UserCreateDto();

        $userDto->email = $request->input('email');
        $userDto->password = Str::random(8);
        $user = $userDto->create();

        Auth::login($user);

        return $this->getPaymentLink($request);
    }

    private function generateRobokassaLink(TransactionDto $transactionDto): string
    {
        $merchant_login = config('app.robokassa.login');

        $password_1 = config('app.robokassa.pass_1');

        $description = 'Покупка в магазине';

//        $userId = auth()->id();

        $shpUser = "Shp_user={$transactionDto->uuid}";

        $signature = md5("$merchant_login:{$transactionDto->amount}::$password_1:$shpUser");

        return "https://auth.robokassa.ru/Merchant/Index.aspx?MerchantLogin={$merchant_login}&OutSum={$transactionDto->amount}&$shpUser&Description=$description&SignatureValue=$signature";
    }

    public function generateFreeKassaLink(TransactionDto $transactionDto): string
    {
        $merchant_id = config('app.freekassa.id');
        $secret_word = config('app.freekassa.secret');
        $order_id = $transactionDto->uuid;
        $order_amount = $transactionDto->amount;
        $currency = 'RUB';
        $sign = md5($merchant_id.':'.$order_amount.':'.$secret_word.':'.$currency.':'.$order_id);

        $link = "https://pay.freekassa.ru/?m={$merchant_id}&oa={$order_amount}&currency={$currency}&o={$order_id}&s={$sign}";

        return $link;
    }
    public function generatePayeerLink(TransactionDto $transactionDto): string
    {
        $m_shop = config('app.payer.m_shop');;
//        $m_orderid = \auth()->id();
        $m_orderid = $transactionDto->uuid;
        $m_amount = number_format($transactionDto->amount, 2, '.', '');
        $m_curr = 'RUB';
        $m_desc = base64_encode('Invoice #' . $transactionDto->uuid);
        $m_key = config('app.payer.key');

        $arHash = array(
            $m_shop,
            $m_orderid,
            $m_amount,
            $m_curr,
            $m_desc
        );

        $arParams = array(
            'success_url' => route('api.payeer.webhook'),
//            'success_url' => 'http://dev.babama.ru/new_success_url',
            'fail_url' => route('api.payeer.webhook'),
            'status_url' => route('api.payeer.webhook'),
            'reference' => array(
                'var1' => '1',
                //'var2' => '2',
                //'var3' => '3',
                //'var4' => '4',
                //'var5' => '5',
            ),
            //'submerchant' => 'mail.com',
        );

        $key = md5($m_key);

        $m_params = @urlencode(base64_encode(openssl_encrypt(json_encode($arParams), 'AES-256-CBC', $key, OPENSSL_RAW_DATA)));

//        $arHash[] = $m_params;

        $arHash[] = $m_key;

        $sign = strtoupper(hash('sha256', implode(':', $arHash)));
        return "https://payeer.com/merchant/?m_shop=$m_shop&m_orderid=$m_orderid&m_amount=$m_amount&m_curr=$m_curr&m_desc=$m_desc&m_sign=$sign";

    }
}
