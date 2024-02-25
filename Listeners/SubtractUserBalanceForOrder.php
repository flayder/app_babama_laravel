<?php

namespace App\Listeners;

use App\Events\OrderCreated;

class SubtractUserBalanceForOrder
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function handle(OrderCreated $event)
    {
        $user = $event->order->user;
        $user->balance -= $event->order->price;
        $user->save();
    }
}
