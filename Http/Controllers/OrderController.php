<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Traits\Notify;
use App\Models\Category;
use App\Models\Order;
use App\Models\Transaction;
use App\Models\User;
use App\Services\Balance;
use Illuminate\Http\Request;
use Stevebauman\Purify\Facades\Purify;

class OrderController extends Controller
{
    use Notify;

    /*
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $page_title = 'All Orders';

        $orders = Order::with('service', 'user', 'parameter')->has('service')->orderByDesc('created_at')->paginate(config('basic..paginate'));

        return view('admin.pages.order.show', compact('orders', 'page_title'));
    }

    /*
     * search
     */
    public function search(Request $request)
    {
        $search = $request->all();
        $dateSearch = $request->date_time;
        $date = $dateSearch ? preg_match("/^[0-9]{2,4}\-[0-9]{1,2}\-[0-9]{1,2}$/", $dateSearch) : null;

        $orders = Order::with('service', 'user')
            ->when(isset($search['order_id']), fn ($query) => $query->where('id', 'LIKE', "%{$search['order_id']}%"))
            ->when(isset($search['service']), fn ($query) => $query->whereHas('service', fn ($q) => $q->where('service_title', 'LIKE', "%{$search['service']}%")))
            ->when(isset($search['user']), function ($query) use ($search) {
                return $query->whereHas('user', function ($q) use ($search): void {
                    $q->where('email', 'LIKE', "%{$search['user']}%")
                        ->orWhere('username', 'LIKE', "%{$search['user']}%");
                });
            })
            ->when(isset($search['status']), fn ($query) => $query->where('status', 'LIKE', "%{$search['status']}%"))
            ->when(1 == $date, fn ($query) => $query->whereDate('created_at', $dateSearch))
            ->paginate(config('basic.paginate'));

        $page_title = 'Search Orders';

        return view('admin.pages.order.search', compact('orders', 'page_title'));
    }

    public function awaiting(Request $request, $name = 'awaiting')
    {
        $page_title = 'Awaiting Orders';
        $orders = Order::with('service', 'user')->where('status', $name)->paginate(config('basic.paginate'));

        return view('admin.pages.order.show', compact('orders', 'page_title'));
    }

    public function pending(Request $request, $name = 'pending')
    {
        $page_title = 'Pending Orders';
        $orders = Order::with('service', 'user')->where('status', $name)->paginate(config('basic.paginate'));

        return view('admin.pages.order.show', compact('orders', 'page_title'));
    }

    public function processing(Request $request, $name = 'processing')
    {
        $page_title = 'Processing Orders';
        $orders = Order::with('service', 'user')->where('status', $name)->paginate(config('basic.paginate'));

        return view('admin.pages.order.show', compact('orders', 'page_title'));
    }

    public function progress(Request $request, $name = 'progress')
    {
        $page_title = 'Progress Orders';
        $orders = Order::with('service', 'user')->where('status', $name)->paginate(config('basic.paginate'));

        return view('admin.pages.order.show', compact('orders', 'page_title'));
    }

    public function completed(Request $request, $name = 'completed')
    {
        $page_title = 'Completed Orders';
        $orders = Order::with('service', 'user')->where('status', $name)->paginate(config('basic.paginate'));

        return view('admin.pages.order.show', compact('orders', 'page_title'));
    }

    public function partial(Request $request, $name = 'partial')
    {
        $page_title = 'Partial Orders';
        $orders = Order::with('service', 'user')->where('status', $name)->paginate(config('basic.paginate'));

        return view('admin.pages.order.show', compact('orders', 'page_title'));
    }

    public function canceled(Request $request, $name = 'canceled')
    {
        $page_title = 'Canceled Orders';
        $orders = Order::with('service', 'user')->where('status', $name)->paginate(config('basic.paginate'));

        return view('admin.pages.order.show', compact('orders', 'page_title'));
    }

    public function refunded(Request $request, $name = 'refunded')
    {
        $page_title = 'Refunded Orders';
        $orders = Order::with('service', 'user')->where('status', $name)->paginate(config('basic.paginate'));

        return view('admin.pages.order.show', compact('orders', 'page_title'));
    }

    /*
     * Show the form for editing the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(Order $order, $id)
    {
        $order = Order::with('user')->find($id);
        $categories = Category::with('services')->has('services')->get();

        return view('admin.pages.order.edit', compact('order', 'categories'));
    }

    /*
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Order $order, $id)
    {
        $req = ($request->all());
        $order = Order::with('user')->find($id);
        $order->start_counter = '' == $req['start_counter'] ? null : $req['start_counter'];
        $order->api_order_id = $request->api_order_id;
        $order->link = $req['link'];
        $order->remains = '' == $req['remains'] ? null : $req['remains'];
        if ($request->status) {
            $order->status = $req['status'];
        }
        $order->reason = $req['reason'];
        $order->save();

        $this->sendMailSms($order->user, 'ORDER_UPDATE', [
            'order_id' => $order->id,
            'start_counter' => $order->start_counter,
            'link' => $order->link,
            'remains' => $order->remains,
            'order_status' => $order->status,
        ]);

        return back()->with('success', 'successfully updated');
    }

    /*
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $order = Order::find($id);

        $order->delete();

        return back()->with('success', 'Successfully Deleted');
    }

    public function statusChange(Request $request)
    {
        $req = $request->all();
        $order = Order::find($request->id);
        $balance = new Balance($order->user);
        if ($req['statusChange'] == 'canceled') {
            $balance->add($order->price);
        }

        $order->status = $req['statusChange'];
        $order->save();

        return back()->with('success', 'Successfully Updated');
    }

    public function getuser(Request $request)
    {
        $user = User::where('name', 'LIKE', "%{$request->user}%")->get()->pluck('name');

        return response()->json($user);
    }

    /*
     * user drop search
     */
    public function getusersearch(Request $request)
    {
        $user = User::where('name', 'LIKE', "%{$request->user_name}%")->get()->pluck('name');

        return response()->json($user);
    }

    /*
     * user search
     */
    public function getTrxUserSearch(Request $request)
    {
        $users = User::where('name', 'LIKE', "%{$request->data}%")->get()->pluck('name');

        return response()->json($users);
    }

    /*
     * TRX
     */
    public function gettrxidsearch(Request $request)
    {
        $transaction = Transaction::where('trx_id', 'LIKE', "%{$request->trxid}%")->get()->pluck('trx_id');

        return response()->json($transaction);
    }

    public function transaction()
    {
        $transaction = Transaction::with('user')->orderBy('id', 'DESC')->paginate(config('basic.paginate'));

        return view('admin.pages.transaction.index', compact('transaction'));
    }

    /*
     * transaction search
     */
    public function transactionSearch(Request $request)
    {
        $search = $request->all();

        $dateSearch = $request->datetrx;
        $date = preg_match("/^[0-9]{2,4}\-[0-9]{1,2}\-[0-9]{1,2}$/", $dateSearch);
        $transaction = Transaction::with('user')->orderBy('id', 'DESC')
            ->when($search['transaction_id'], fn ($query) => $query->where('trx_id', 'LIKE', "%{$search['transaction_id']}%"))
            ->when($search['user_name'], function ($query) use ($search) {
                return $query->whereHas('user', function ($q) use ($search): void {
                    $q->where('email', 'LIKE', "%{$search['user_name']}%")
                        ->orWhere('username', 'LIKE', "%{$search['user_name']}%");
                });
            })
            ->when($search['remark'], fn ($query) => $query->where('remarks', 'LIKE', "%{$search['remark']}%"))
            ->when(1 == $date, fn ($query) => $query->whereDate('created_at', $dateSearch))
            ->paginate(config('basic.paginate'));
        $transaction = $transaction->appends($search);

        return view('admin.pages.transaction.index', compact('transaction'));
    }
}
