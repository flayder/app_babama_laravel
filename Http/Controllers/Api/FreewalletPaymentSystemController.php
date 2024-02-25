<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\DB;

use App\Jobs\FreewalletPaymentJob;
use App\Http\Controllers\Controller;
use App\Http\Resources\FreewalletPaymentSystemResource;
use App\Models\FreewalletPayment;
use App\Models\User;

use App\Models\FreewalletPaymentSystem;

use Illuminate\Http\Request;
use App\Repositories\ReferralBalanceInfoRepository;
use App\Services\FreewalletService;
use Carbon\Carbon;

class FreewalletPaymentSystemController extends Controller
{
    public function index(Request $request)
    {
        $payments = FreewalletPaymentSystem::orderBy('name', 'ASC')->get();

        return FreewalletPaymentSystemResource::collection($payments);
    }
}
