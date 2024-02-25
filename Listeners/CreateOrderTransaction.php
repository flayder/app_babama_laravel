<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Models\Transaction;

class CreateOrderTransaction
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

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(OrderCreated $event)
    {
        Transaction::create([
            'user_id' => $event->order->user_id,
            'trx_type' => '-',
            'amount' => $event->order->price,
            'remarks' => 'Заказ',
            'trx_id' => strRandom(),
        ]);

    }
}
