<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Http\Traits\Notify;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotifyAdminAboutOrderCreation implements ShouldQueue
{
    use Notify;

    use InteractsWithQueue;

    public int $tries = 3;

    public function handle(OrderCreated $event)
    {
        $order = $event->order;

        $user = $order->user;

        $basic = (object)config('basic');

        $msg = [
            'username' => $user->username,
            'price' => $order->price,
            'currency' => $basic->currency,
        ];

        $action = [
            'link' => route('admin.order.edit', $order->id),
            'icon' => 'fas fa-cart-plus text-white',
        ];

        $this->adminPushNotification('ORDER_CREATE', $msg, $action);
    }
}
