<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Http\Traits\Notify;
use Illuminate\Queue\InteractsWithQueue;

class NotifyUserAboutOrderCreation
{
    use Notify;

    use InteractsWithQueue;

    public int $tries = 3;
    public function handle(OrderCreated $event): void
    {
        $order = $event->order;

        $user = $order->user;

        $basic = (object)config('basic');

        $this->sendMailSms($user, 'ORDER_CONFIRM', [
            'order_id' => $order->id,
            'order_at' => $order->created_at,
            'service' => optional($order->service)->service_title,
            'status' => $order->status,
            'paid_amount' => $order->price,
            'remaining_balance' => $user->balance,
            'currency' => $basic->currency,
            'transaction' => '-------',
        ]);
    }
}
