<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\Notify;
use App\Models\Fund;
use Facades\App\Services\BasicService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Stevebauman\Purify\Facades\Purify;

class PaymentLogController extends Controller
{
    use Notify;

    public function index()
    {
        $page_title = 'Payment Logs';
        $funds = Fund::where('status', '!=', 0)->orderBy('id', 'DESC')->with('user', 'gateway')->paginate(config('basic.paginate'));

        return view('admin.pages.payment.logs', compact('funds', 'page_title'));
    }

    public function search(Request $request)
    {
        $search = $request->all();

        $dateSearch = $request->date_time;
        $date = preg_match("/^[0-9]{2,4}\-[0-9]{1,2}\-[0-9]{1,2}$/", $dateSearch);

        $funds = Fund::when(isset($search['name']), function ($query) use ($search) {
            return $query->where('transaction', 'LIKE', $search['name'])
                ->orWhereHas('user', function ($q) use ($search): void {
                    $q->where('email', 'LIKE', "%{$search['name']}%")
                        ->orWhere('username', 'LIKE', "%{$search['name']}%");
                });
        })
            ->when(1 == $date, fn ($query) => $query->whereDate('created_at', $dateSearch))
            ->when(-1 != $search['status'], fn ($query) => $query->where('status', $search['status']))
            ->where('status', '!=', 0)
            ->with('user', 'gateway')
            ->paginate(config('basic.paginate'));
        $funds->appends($search);
        $page_title = 'Search Payment Logs';

        return view('admin.pages.payment.logs', compact('funds', 'page_title'));
    }

    public function action(Request $request, $id)
    {
        $this->validate($request, [
            'id' => 'required',
            'status' => ['required', Rule::in(['1', '3'])],
        ]);
        $data = Fund::where('id', $request->id)->whereIn('status', [2])->with('user', 'gateway')->firstOrFail();
        $basic = (object) config('basic');

        $req = $request->all();
        $req = (object) $req;

        $user = $data->user;

        if ('1' == $request->status) {
            $data->feedback = $request->feedback;
            $data->update();
            BasicService::preparePaymentUpgradation($data);

            $msg = [
                'amount' => getAmount($data->amount),
                'currency' => $basic->currency,
                'feedback' => $data->feedback,
            ];
            $action = [
                'link' => '#',
                'icon' => 'fas fa-money-bill-alt text-white',
            ];
            $this->userPushNotification($user, 'PAYMENT_APPROVED', $msg, $action);

            session()->flash('success', 'Approve Successfully');

            return back();
        } elseif ('3' == $request->status) {
            $data->status = 3;
            $data->feedback = $request->feedback;
            $data->update();

            $this->sendMailSms($user, $type = 'DEPOSIT_REJECTED', [
                'amount' => getAmount($data->amount),
                'currency' => $basic->currency,
                'method' => optional($data->gateway)->name,
                'transaction' => $data->transaction,
                'feedback' => $data->feedback,
            ]);

            $msg = [
                'amount' => getAmount($data->amount),
                'currency' => $basic->currency,
                'feedback' => $data->feedback,
            ];
            $action = [
                'link' => '#',
                'icon' => 'fas fa-money-bill-alt text-white',
            ];
            $this->userPushNotification($user, 'PAYMENT_REJECTED', $msg, $action);

            session()->flash('success', 'Reject Successfully');

            return back();
        }
    }
}
