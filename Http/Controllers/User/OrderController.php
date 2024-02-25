<?php

declare(strict_types=1);

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Traits\Notify;
use App\Models\ApiProvider;
use App\Models\Category;
use App\Models\Order;
use App\Models\Service;
use App\Models\Transaction;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Ixudra\Curl\Facades\Curl;
use Stevebauman\Purify\Facades\Purify;

class OrderController extends Controller
{
    use Notify;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->user = auth()->user();

            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $orders = Order::with(['users', 'service'])->latest()->where('user_id', Auth::id())->paginate();

        return view('user.pages.order.show', compact('orders'));
    }

    public function search(Request $request)
    {
        $search = @$request->search;
        $status = @$request->status;
        $dateSearch = @$request->date_order;

        $date = preg_match("/^[0-9]{2,4}\-[0-9]{1,2}\-[0-9]{1,2}$/", $dateSearch);
        $orders = Order::where('user_id', Auth::id())
            ->when($search, function ($query) use ($search) {
                return $query->where('id', 'LIKE', "%{$search}%")
                    ->orWhereHas('service', fn ($q) => $q->where('service_title', 'LIKE', "%{$search}%"));
            })
            ->when(-1 != $status, fn ($query) => $query->where('status', 'LIKE', "%{$status}%"))
            ->when(1 == $date, fn ($query) => $query->whereDate('created_at', $dateSearch))
            ->with('service', 'service.category', 'users')
            ->latest()
            ->paginate(config('basic.paginate'));

        return view('user.pages.order.show', compact('orders'));
    }

    public function statusSearch(Request $request, $name = 'awaiting')
    {
        $status = @$name;
        $orders = Order::with('service', 'users')
            ->where(['user_id' => Auth::id()])
            ->when(-1 != $status, fn ($query) => $query->where('status', $status))
            ->paginate(config('basic.paginate'));

        return view('user.pages.order.show', compact('orders'));
    }

    public function create(Request $request): View|Factory|Application
    {
        $serviceId = @$request->serviceId;

        if (isset($serviceId)) {
            $data['selectService'] = Service::where('service_status', 1)->userRate()->with('category')->find($serviceId);
        } else {
            $data['selectService'] = null;
        }

        $data['categories'] = Category::with('services')
            ->whereHas('services', function ($query): void {
                $query->where('service_status', 1)->userRate();
            })
            ->get();

        return view('user.pages.order.add', $data, compact('serviceId'));
    }

    public function userservice(Request $request)
    {
        $serid = $request->ser_id;
        $service = Service::where('id', $serid)->userRate()->first();

        return $service;
    }

    public function store(Request $request): RedirectResponse
    {
        $req = ($request->all());
        $rules = [
            'category' => 'required|integer|min:1|not_in:0',
            'service' => 'required|integer|min:1|not_in:0',
            'link' => 'required|url',
            'quantity' => 'required|integer',
            'check' => 'required',
        ];
        if (!isset($request->drip_feed)) {
            $rules['runs'] = 'required|integer|not_in:0';
            $rules['interval'] = 'required|integer|not_in:0';
        }
        $validator = Validator::make($req, $rules);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        $service = Service::userRate()->findOrFail($request->service);

        $basic = (object) config('basic');

        $quantity = $request->quantity;

        if (1 == $service->drip_feed) {
            if (!isset($request->drip_feed)) {
                $rules['runs'] = 'required|integer|not_in:0';
                $rules['interval'] = 'required|integer|not_in:0';
                $validator = Validator::make($req, $rules);
                if ($validator->fails()) {
                    return back()->withErrors($validator)->withInput();
                }
                $quantity = $request->quantity * $request->runs;
            }
        }
        if ($service->min_amount <= $quantity && $service->max_amount >= $quantity) {
            $userRate = $service->user_rate ?? $service->price;
            $price = round(($quantity * $userRate) / 1000, $basic->fraction_number);

            $user = Auth::user();
            if ($user->balance < $price) {
                return back()->with('error', 'Insufficient balance in your wallet.')->withInput();
            }
            $order = new Order();
            $order->user_id = $user->id;
            $order->category_id = $req['category'];
            $order->service_id = $req['service'];
            $order->link = $req['link'];
            $order->quantity = $req['quantity'];
            $order->status = 'processing';
            $order->price = $price;
            $order->runs = isset($req['runs']) && !empty($req['runs']) ? $req['runs'] : null;
            $order->interval = isset($req['interval']) && !empty($req['interval']) ? $req['interval'] : null;

            if (isset($service->api_provider_id)) {
                $apiproviderdata = ApiProvider::find($service->api_provider_id);
                $postData = [
                    'key' => $apiproviderdata['api_key'],
                    'action' => 'add',
                    'service' => $service->api_service_id,
                    'link' => $req['link'],
                    'quantity' => $req['quantity'],
                ];

                if (isset($req['runs'])) {
                    $postData['runs'] = $req['runs'];
                }

                if (isset($req['interval'])) {
                    $postData['interval'] = $req['interval'];
                }

                $apiservicedata = Curl::to($apiproviderdata['url'])->withData($postData)->post();
                $apidata = json_decode($apiservicedata);
                if (isset($apidata->order)) {
                    $order->status_description = "order: {$apidata->order}";
                    $order->api_order_id = $apidata->order;
                } else {
                    $order->status_description = "error: {$apidata->error}";
                }
            }
            $order->save();
            $user->balance -= $price;
            $user->save();

            $transaction = new Transaction();
            $transaction->user_id = $user->id;
            $transaction->trx_type = '-';
            $transaction->amount = $price;
            $transaction->remarks = 'Заказ';
            $transaction->trx_id = strRandom();
            $transaction->charge = 0;
            $transaction->save();

            $msg = [
                'username' => $user->username,
                'price' => $price,
                'currency' => $basic->currency,
            ];
            $action = [
                'link' => route('admin.order.edit', $order->id),
                'icon' => 'fas fa-cart-plus text-white',
            ];
            $this->adminPushNotification('ORDER_CREATE', $msg, $action);

            $this->sendMailSms($user, 'ORDER_CONFIRM', [
                'order_id' => $order->id,
                'order_at' => $order->created_at,
                'service' => optional($order->service)->service_title,
                'status' => $order->status,
                'paid_amount' => $price,
                'remaining_balance' => $user->balance,
                'currency' => $basic->currency,
                'transaction' => $transaction->trx_id,
            ]);

            return back()->with('success', 'Your order has been submitted');
        } else {
            return back()->with('error', "Order quantity should be minimum {$service->min_amount} and maximum {$service->max_amount}")->withInput();
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return Response
     */
    public function edit(Order $order)
    {
        $order = Order::find($order->id);

        return view('user.pages.order.edit', compact('order'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return Response
     */
    public function destroy(Order $order)
    {
        $order = Order::find($order->id);
        $order->delete();

        return back()->with('success', 'Successfully Deleted');
    }

    public function statusChange(Request $request)
    {
        $req = ($request->all());
        $order = Order::find($request->id);
        $order->status = $req['statusChange'];
        $order->save();

        return back()->with('success', 'Successfully Updated');
    }

    public function getservice(Request $request)
    {
        $service = Service::where('service_status')->where('service_title', 'LIKE', "%{$request->service}%")->get()->pluck('service_title');

        return response()->json($service);
    }

    public function massOrder()
    {
        return view('user.pages.order.add_mass_order');
    }

    public function masOrderStore(Request $request)
    {
        $req = $request->all();
        $rules = [
            'mass_order' => 'required',
        ];
        $validator = Validator::make($req, $rules);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        $orders = explode("\r\n", $req['mass_order']);

        $basic = (object) config('basic');

        foreach ($orders as $order) {
            $singleOrder = explode('|', trim($order));
            if (3 != \count($singleOrder)) {
                continue;
            }

            if (0 != fmod($singleOrder[0], 1) || 0 != fmod($singleOrder[1], 1)) {
                continue;
            }

            $serviceid = Service::userRate()->find($singleOrder[0]);
            if ($serviceid) {
                $specificRate = (float) ($serviceid->user_rate ?? $serviceid->price);

                $orderM = new Order();
                $orderM->service_id = $singleOrder[0];
                $orderM->category_id = $serviceid->category_id;
                $orderM->quantity = $singleOrder[1];
                $orderM->link = $singleOrder[2];

                $price = round(((float) $singleOrder[1] * $specificRate) / 1000, $basic->fraction_number);
                $orderM->price = $price;
                $user = $this->user;
                $orderM->user_id = $user->id;

                if (1 == $serviceid->service_status) {
                    if (isset($singleOrder[1]) && !empty($singleOrder[1]) && $singleOrder[1] % 1 == 0) {
                        if ($serviceid->min_amount <= $singleOrder[1] && $serviceid->max_amount >= $singleOrder[1]) {
                            if (isset($singleOrder[2]) && !empty($singleOrder[2])) {
                                if ($user->balance >= $orderM->price) {
                                    $user->balance -= $orderM->price;
                                    $user->save();

                                    $orderM->status = 'pending';

                                    if (isset($service->api_provider_id)) {
                                        $apiproviderdata = ApiProvider::find($serviceid->api_provider_id);
                                        if ($apiproviderdata) {
                                            $apiservicedata = Curl::to($singleOrder[2])->withData(['key' => $apiproviderdata['api_key'], 'action' => 'add', 'service' => $serviceid->api_service_id, 'link' => $singleOrder[2], 'quantity' => $singleOrder[1]])->post();
                                            $apidata = json_decode($apiservicedata);
                                            if (isset($apidata->order)) {
                                                $orderM->status_description = "order: {$apidata->order}";
                                                $orderM->api_order_id = $apidata->order;
                                                $orderM->status = 'progress';
                                            } else {
                                                $orderM->status_description = "error: {$apidata->error}";
                                            }
                                        }
                                    }
                                    $orderM->save();

                                    $transaction = new Transaction();
                                    $transaction->user_id = $user->id;
                                    $transaction->trx_type = '-';
                                    $transaction->amount = $orderM->price;
                                    $transaction->charge = 0;
                                    $transaction->remarks = 'Заказ';
                                    $transaction->trx_id = strRandom();
                                    $transaction->save();

                                    $this->sendMailSms($user, 'ORDER_CONFIRM', [
                                        'order_id' => $orderM->id,
                                        'order_at' => $orderM->created_at,
                                        'service' => optional($orderM->service)->service_title,
                                        'status' => $orderM->status,
                                        'paid_amount' => $orderM->price,
                                        'remaining_balance' => $user->balance,
                                        'currency' => $basic->currency,
                                        'transaction' => $transaction->trx_id,
                                    ]);

                                    $msg = ['username' => $user->username, 'price' => $orderM->price, 'currency' => $basic->currency];
                                    $action = [
                                        'link' => route('admin.order.edit', $orderM->id),
                                        'icon' => 'fas fa-cart-plus text-white',
                                    ];
                                    $this->adminPushNotification('ORDER_CREATE', $msg, $action);
                                } else {
                                    $orderM->reason = 'Insufficient balance in your wallet';
                                    $orderM->status = 'canceled';
                                }
                            } else {
                                $orderM->reason = 'Link is Invalid';
                                $orderM->status = 'canceled';
                            }
                        } else {
                            $orderM->reason = "Order quantity should be minimum {$serviceid->min_amount} and maximum {$serviceid->max_amount}";
                            $orderM->status = 'canceled';
                        }
                    } else {
                        $orderM->reason = 'Invalid Quantity';
                        $orderM->status = 'canceled';
                    }
                } else {
                    $orderM->reason = 'Service not available';
                    $orderM->status = 'canceled';
                }
                $orderM->save();
            }
        }

        return back()->with('success', 'Successfully Added');
    }
}
