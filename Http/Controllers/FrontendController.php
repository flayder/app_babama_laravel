<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Traits\Notify;
use App\Mail\SendMail;
use App\Models\Category;
use App\Models\Content;
use App\Models\ContentDetails;
use App\Models\Gateway;
use App\Models\Language;
use App\Models\Order;
use App\Models\Service;
use App\Models\Subscriber;
use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Ixudra\Curl\Facades\Curl;
use Stevebauman\Purify\Facades\Purify;

class FrontendController extends Controller
{
    use Notify;

    public function __construct()
    {
        $this->theme = 'themes.minimal.';
    }

    public function index(): string
    {
        return File::get(public_path('vue/index.html'));
    }

    public function referral(Request $request, string $code): string
    {
        echo $code;
    }


    public function cron()
    {
        $orders = Order::with(['service', 'service.provider'])->whereNotIn('status', ['completed', 'refunded', 'canceled'])->whereHas('service', function ($query): void {
            $query->whereNotNull('api_provider_id')->orWhere('api_provider_id', '!=', 0);
        })->get();

        foreach ($orders as $order) {
            $service = $order->service;
            if (isset($service->api_provider_id)) {
                $apiproviderdata = $service->provider;
                $apiservicedata = Curl::to($apiproviderdata['url'])->withData(['key' => $apiproviderdata['api_key'], 'action' => 'status', 'order' => $order->api_order_id])->post();
                $apidata = json_decode($apiservicedata);
                if (isset($apidata->status)) {
                    $order->status = strtolower($apidata->status);
                    $order->start_counter = @$apidata->start_count;
                    $order->remains = @$apidata->remains;
                }

                if (isset($apidata->error)) {
                    $order->status_description = 'error: {'.@$apidata->error.'}';
                }

                $order->save();
            }
        }

        return 'ok';
    }
}
