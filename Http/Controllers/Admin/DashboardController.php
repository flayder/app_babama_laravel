<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\TransactionResource;
use App\Http\Traits\Upload;
use App\Models\ApiProvider;
use App\Models\Configure;
use App\Models\Fund;
use App\Models\Order;
use App\Models\Referral;
use App\Models\Ticket;
use App\Models\Transaction;
use App\Models\User;
use App\Modules\Transaction\Enums\TransactionStatusEnum;
use App\Modules\Transaction\Enums\TransactionTypeEnum;
use App\Rules\FileTypeValidate;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class DashboardController extends Controller
{
    use Upload;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->user = Auth::guard('admin')->user();

            return $next($request);
        });
    }

    public function dashboard()
    {
        $last30 = Carbon::now()->subDays(30);
        $data['totalAmountReceived'] = Fund::where('status', 1)->sum('amount');
        $data['totalOrder'] = Order::count();
        $data['totalProviders'] = ApiProvider::count();

        $users = User::selectRaw(('COUNT(id) AS total_user'))
            ->selectRaw(('SUM(balance) AS total_user_balance'))
            ->selectRaw(('COUNT((CASE WHEN created_at >= NOW()  THEN id END)) AS today_join'))
            ->get()->makeHidden(['fullname', 'mobile'])->toArray();

        foreach ($users as $k=>$user) {
            $users[$k]['totalUser'] = $user['total_user'];
            $users[$k]['totalUserBalance'] = $user['total_user_balance'];
            $users[$k]['todayJoin'] = $user['today_join'];
        }

        $data['userRecord'] = collect($users)->collapse();

        $transactionsSelect = [
          0 => DB::raw('SUM((CASE WHEN type LIKE \'' . TransactionTypeEnum::PAY->value . '\' AND created_at >=(NOW() - INTERVAL \'1 months\') THEN amount WHEN status LIKE \'' . TransactionStatusEnum::SUCCESS->value . '\' AND created_at >= (NOW() - INTERVAL \'1 months\') THEN amount END)) AS profit_30_days'),
          1 => DB::raw('SUM((CASE WHEN type LIKE \'' . TransactionTypeEnum::PAY->value . '\' AND created_at >= NOW() THEN amount WHEN status LIKE \'' . TransactionStatusEnum::SUCCESS->value . '\' AND created_at >= NOW() THEN amount END)) AS profit_today')
        ];
        $transactions = Transaction::selectRaw($transactionsSelect[0])
            ->selectRaw($transactionsSelect[1])
            ->get()->toArray();
        $data['transactionProfit'] = collect($transactions)->collapse();

        $tickets = Ticket::where('created_at', '>', Carbon::now()->subDays(30))
            ->selectRaw('count(CASE WHEN status = 3  THEN status END) AS closed')
            ->selectRaw('count(CASE WHEN status = 2  THEN status END) AS replied')
            ->selectRaw('count(CASE WHEN status = 1  THEN status END) AS answered')
            ->selectRaw('count(CASE WHEN status = 0  THEN status END) AS pending')
            ->get()->toArray();
        $data['tickets'] = collect($tickets)->collapse();

        $orders = Order::where('created_at', '>', Carbon::now()->subDays(30))
            ->selectRaw('count(id) as totalOrder')
            ->selectRaw('count(CASE WHEN status = \'completed\'  THEN status END) AS completed')
            ->selectRaw('count(CASE WHEN status = \'processing\'  THEN status END) AS processing')
            ->selectRaw('count(CASE WHEN status = \'pending\'  THEN status END) AS pending')
            ->selectRaw('count(CASE WHEN status = \'progress\'  THEN status END) AS inProgress')
            ->selectRaw('count(CASE WHEN status = \'partial\'  THEN status END) AS partial')
            ->selectRaw('count(CASE WHEN status = \'canceled\'  THEN status END) AS canceled')
            ->selectRaw('count(CASE WHEN status = \'refunded\'  THEN status END) AS refunded')
            ->selectRaw('COUNT((CASE WHEN created_at >= NOW()  THEN id END)) AS todaysOrder')
            ->get()->map(function ($value) {
                return [
                    'records' => [
                        'totalOrder' => $value->totalOrder,
                        'todaysOrder' => $value->todaysOrder,
                        'complete' => $value->completed,
                        'processing' => $value->processing,
                        'pending' => $value->pending,
                        'inProgress' => $value->inProgress,
                        'partial' => $value->partial,
                        'canceled' => $value->canceled,
                        'refunded' => $value->refunded,
                    ],
                    'percent' => [
                        'complete' => ($value->completed && $value->totalOrder > 0) ? round(($value->completed / $value->totalOrder) * 100, 2) : 0,
                        'processing' => ($value->processing && $value->totalOrder > 0) ? round(($value->processing / $value->totalOrder) * 100, 2) : 0,
                        'pending' => ($value->pending && $value->totalOrder > 0) ? round(($value->pending / $value->totalOrder) * 100, 2) : 0,
                        'inProgress' => ($value->inProgress && $value->totalOrder > 0) ? round(($value->inProgress / $value->totalOrder) * 100, 2) : 0,
                        'partial' => ($value->partial && $value->totalOrder > 0) ? round(($value->partial / $value->totalOrder) * 100, 2) : 0,
                        'canceled' => ($value->canceled && $value->totalOrder > 0) ? round(($value->canceled / $value->totalOrder) * 100, 2) : 0,
                        'refunded' => ($value->refunded && $value->totalOrder > 0) ? round(($value->refunded / $value->totalOrder) * 100, 2) : 0,
                    ],
                ];
            });

        $data['orders'] = collect($orders)->collapse();

        $data['bestSale'] = Order::with('service')
            ->selectRaw('service_id ,COUNT(service_id) as count, sum(quantity) as quantity')
            ->groupBy('service_id')->orderBy('count', 'DESC')->take(10)->get();

        $orderStatistics = Order::where('created_at', '>', Carbon::now()->subDays(30))
            ->selectRaw('count(CASE WHEN status = \'completed\'  THEN status END) AS completed')
            ->selectRaw('count(CASE WHEN status = \'processing\'  THEN status END) AS processing')
            ->selectRaw('count(CASE WHEN status = \'pending\'  THEN status END) AS pending')
            ->selectRaw('count(CASE WHEN status = \'progress\'  THEN status END) AS progress')
            ->selectRaw('count(CASE WHEN status = \'partial\'  THEN status END) AS partial')
            ->selectRaw('count(CASE WHEN status = \'canceled\'  THEN status END) AS canceled')
            ->selectRaw('count(CASE WHEN status = \'refunded\'  THEN status END) AS refunded')
            ->selectRaw('to_char(created_at, \'yyyy-mm-dd hh24:mi:ss\') as date')
            ->orderBy('created_at')
            ->groupBy(DB::raw('(created_at)'))->get();

        $statistics['date'] = [];
        $statistics['completed'] = [];
        $statistics['processing'] = [];
        $statistics['pending'] = [];
        $statistics['progress'] = [];
        $statistics['partial'] = [];
        $statistics['canceled'] = [];
        $statistics['refunded'] = [];
        foreach ($orderStatistics as $k => $val) {
            $statistics['date'][] = trim($val->date);
            $statistics['completed'][] = (null != $val->completed) ? $val->completed : 0;
            $statistics['processing'][] = (null != $val->processing) ? $val->processing : 0;
            $statistics['pending'][] = (null != $val->pending) ? $val->pending : 0;
            $statistics['progress'][] = (null != $val->progress) ? $val->progress : 0;
            $statistics['partial'][] = (null != $val->partial) ? $val->partial : 0;
            $statistics['canceled'][] = (null != $val->canceled) ? $val->canceled : 0;
            $statistics['refunded'][] = (null != $val->refunded) ? $val->refunded : 0;
        }

        $data['latestUser'] = User::latest()->limit(5)->get();

        return view('admin.pages.dashboard', $data, compact('statistics'));
    }

    public function referralCommission()
    {
        $data['referrals'] = Referral::get();
        $data['control'] = Configure::firstOrNew();

        return view('admin.pages.referral-commission', $data);
    }

    public function referralCommissionStore(Request $request)
    {
        $request->validate([
            'level*' => 'required|integer|min:1',
            'percent*' => 'required|numeric',
            'commission_type' => 'required',
        ]);

        Referral::where('commission_type', $request->commission_type)->delete();
        for ($i = 0; $i < \count($request->level); ++$i) {
            $referral = new Referral();
            $referral->commission_type = $request->commission_type;
            $referral->level = $request->level[$i];
            $referral->percent = $request->percent[$i];
            $referral->save();
        }

        return back()->with('success', 'Level Bonus Has been Updated.');
    }

    public function referralCommissionAction(Request $request)
    {
        $configure = Configure::firstOrNew();
        $reqData = $request->except('_token', '_method');

        $fp = fopen(base_path().'/config/basic.php', 'w');
        fwrite($fp, '<?php return '.var_export(config('basic'), true).';');
        fclose($fp);

        config(['basic.deposit_commission' => (int) $reqData['deposit_commission']]);
        $configure->fill($reqData)->save();

        return back()->with('success', 'Update Successfully.');
    }

    public function profile()
    {
        $admin = $this->user;

        return view('admin.pages.admin.profile', compact('admin'));
    }

    public function profileUpdate(Request $request)
    {
        $rules = [
            'name' => 'sometimes|required',
            'username' => 'sometimes|required|unique:admins,username,'.$this->user->id,
            'email' => 'sometimes|required|email|unique:admins,email,'.$this->user->id,
            'phone' => 'sometimes|required',
            'address' => 'sometimes|required',
            'image' => ['nullable', 'image', new FileTypeValidate(['jpeg', 'jpg', 'png'])],
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        $user = $this->user;
        if ($request->hasFile('image')) {
            try {
                $old = $user->image ?: null;
                $user->image = $this->uploadImage($request->image, config('location.admin.path'), config('location.admin.size'), $old);
            } catch (\Exception $exp) {
                return back()->with('error', 'Image could not be uploaded.');
            }
        }
        $user->name = $request->name;
        $user->username = $request->username;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->address = $request->address;
        $user->save();

        return back()->with('success', 'Updated Successfully.');
    }

    public function password()
    {
        return view('admin.pages.admin.password');
    }

    public function passwordUpdate(Request $request)
    {
        $req = $request->all();

        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:5|confirmed',
        ]);

        $request = (object) $req;
        $user = $this->user;
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->with('error', "Password didn't match");
        }
        $user->update([
            'password' => bcrypt($request->password),
        ]);

        return back()->with('success', 'Password has been Changed');
    }
}
