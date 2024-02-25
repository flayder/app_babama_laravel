<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\DB;

use App\Jobs\FreewalletPaymentJob;
use App\Http\Controllers\Controller;
use App\Http\Resources\FreewalletPaymentResource;
use App\Models\FreewalletPayment;
use App\Models\User;

use App\Models\FreewalletCurrency;
use App\Models\FreewalletPaymentSystem;

use Illuminate\Http\Request;
use App\Repositories\ReferralBalanceInfoRepository;
use App\Repositories\UserRepository;
use App\Services\FreewalletService;
use Carbon\Carbon;

class FreewalletPaymentController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $payments = FreewalletPayment::where('user_id', $user->id)->orderBy('id', 'DESC')->paginate(3);

        return FreewalletPaymentResource::collection($payments);
    }

    public function store(Request $request)
    {

        $validated = $request->validate([
            'payment_system_id' => 'required|min:1',
            'amount' => 'required|min:1',
            'account' => 'required|min:5'
        ]);

        $user = auth()->user();
        $payment_currency = FreewalletCurrency::first();

        if($payment_currency) {
            $data = [
                'user_id'           => $user->id,
                'payment_system_id' => $validated['payment_system_id'],
                'currency_id'       => $payment_currency->id,
                'amount'            => floatval($validated['amount']),
                'account'           => $validated['account']
            ];

            $freewalletService = new FreewalletService;
            $freewalletService->create($data);
        }

        
        
        return response()->json(['data' => true]);
    }


    //Admin panel confirmation
    public function confirm(Request $request)
    {
    	$user = auth()->user();
    	$id = $request->input('id');

    	if($id > 0) {
        	$payment = FreewalletPayment::find($id);
        	dispatch(new FreewalletPaymentJob($payment));
        }

    }

    public function info(Request $request)
    {
        $user = auth()->user();
        $freewalletService = new FreewalletService;

        $total = $freewalletService->getTotalPrice($user);

        $totalBalance = $freewalletService->getTotalPrice($user, true);

        return response()->json([
            'data' => [
                'referralBalance'   => floatval($user->referral_balance),
                'total'             => floatval($total),
                'totalBalance'      => floatval($totalBalance)
            ]
        ]);
    }
}
