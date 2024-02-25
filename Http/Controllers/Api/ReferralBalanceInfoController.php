<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReferralBalanceInfoResource;
use App\Models\ReferralBalanceInfo;
use App\Models\User;

use App\Models\Transaction;
use App\Jobs\ReferralAddToBalanceJob;

use Illuminate\Http\Request;
use App\Repositories\ReferralBalanceInfoRepository;
use App\Repositories\UserRepository;
use App\Repositories\ReferRepository;
use App\Services\ReferService;
use Carbon\Carbon;

class ReferralBalanceInfoController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $payments = ReferralBalanceInfo::where('referral_id', $user->id)->orderBy('id', 'DESC')->paginate(15);

        return ReferralBalanceInfoResource::collection($payments);
    }
}
