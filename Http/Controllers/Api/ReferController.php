<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReferResource;
use App\Models\Refer;
use App\Models\User;
use App\Models\ReferralBalanceInfo;

use App\Models\Transaction;
use App\Jobs\ReferralAddToBalanceJob;

use Illuminate\Http\Request;
use App\Repositories\ReferralBalanceInfoRepository;
use App\Repositories\UserRepository;
use App\Repositories\ReferRepository;
use App\Services\ReferService;
use Carbon\Carbon;

class ReferController extends Controller
{
    public function index(Request $request): array
    {
        $user = auth()->user();

        $period = $request->input('period');
        $deleted = (bool)$request->input('deleted');
        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');

        $referrals = Refer::where('user_id', $user->id);

        if(!$deleted)
            $referrals->where('deleted', false);


        $referrals = $referrals->orderBy('id', 'DESC')->get();

        $referService = new ReferService(new Refer);
        $transactionRepository = new ReferralBalanceInfoRepository;

        $userRepository = new UserRepository;

        $from_date = false;
        $to_date = false;

        if($period && (!$startDate || !$endDate)) {
            if($period == "today") {
                $from_date = now()->subDays(1);
            } else if($period == "week") {
                $from_date = now()->subDays(7);
            } else if($period == "month") {
                $from_date = now()->subMonths(1);
            } else if($period == "halfyear") {
                $from_date = now()->subMonths(6);
            } else if($period == "year") {
                $from_date = now()->subYear();
            }
        } else if($startDate && $endDate) {
            $from_date = Carbon::createFromFormat('Y-m-d', $startDate);
            $to_date = Carbon::createFromFormat('Y-m-d', $endDate);
        }

        foreach ($referrals as $key => $referral) {
            $users = $referral->users;
            
            if($from_date || $to_date)
                $visits = $userRepository->getUsersByPeriod($users, $from_date, $to_date);
            else
                $visits = $users;
                
            $transactions = $transactionRepository->getTransactionsByPeriod($visits, $from_date, $to_date);
            $balanced = $transactionRepository->getBalanced($visits, $from_date, $to_date);
            $referrals[$key]->visits = count($visits);
            $referrals[$key]->balanced = count($transactions);
            $referrals[$key]->conversion = $referService->calculateConversion(count($visits), count($balanced));
            $referrals[$key]->earn = $referService->calculateReferralTotalEarn($transactions);
        }


        return ReferResource::collection($referrals)->resolve();
    }

    public function referral(Request $request, string $code)
    {
        return response()->json(['data' => true]);
    }

    public function create(Request $request)
    {
        $user = auth()->user();

        $messages = [
            'required' => 'Реферальная ссылка обязательная для заполнения',
            'min'    => 'Минимальное количество символов :attribute',
            'unique' => 'Такая ссылка уже существует'
        ];

        $validated = $request->validate([
            'link' => 'required|min:4|unique:refers',
        ], $messages);

        $refer = new Refer;
        $refer->link = $request->input('link');
        $refer->deleted = 0;
        $refer->user_id = $user->id;

        $refer = $refer->save();

        return response()->json(['data' => true]);
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'deleted' => 'required'
        ]);
        $refer = Refer::findOrFail($id);
        $refer->update(
            $validated
        );

        return ReferResource::make($refer);
    }

    public function info(Request $request)
    {
        $user = auth()->user();
        $referRepository = new ReferRepository;

        $referrals = $referRepository->getReferrals($user);

        $level = 1;
        $total = 0;
        $cashFlow = 0;
        $paymentPercent = 0;

        if($referrals) {
            $referService = new ReferService(new Refer);
            $transactionRepository = new ReferralBalanceInfoRepository;
            $totalOrders = $transactionRepository->getTransactionsByPeriod($referrals);
            $monthTotalOrders = $transactionRepository->getTransactionsByPeriod($referrals, now()->subMonths(1));

            $level = $referService->calculateUserLevel($monthTotalOrders);
            $total = $referService->calculateReferralTotalEarn($totalOrders);
            $cashFlow = $referService->calculateReferralCashFlow($monthTotalOrders);
            $paymentPercent = $referService->getReferralPaymentPercent($level);
        }

        $response = [
            'referralBalance'   => $user->referral_balance,
            'total'             => $total,
            'level'             => $level,
            'cashFlow'          => $cashFlow,
            'paymentPercent'    => $paymentPercent
        ];

        return $response;
    }
}
