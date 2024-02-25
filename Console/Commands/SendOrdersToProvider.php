<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Dto\Order\OrderDto;
use App\Dto\Seller\OrderStatus\OrderStatusSellerSuccessResponseDto;
use App\Dto\Seller\SellerFailedResponseDto;
use App\Enums\OrderStatusEnum;
use App\Helper\SellerHelper;
use App\Jobs\RefundOrder;
use App\Jobs\SendSellerJob;
use App\Models\Order;
use App\Services\Balance;
use App\Services\OrderService;
use App\Services\SellerService;
use Illuminate\Console\Command;
use Ixudra\Curl\Facades\Curl;

class SendOrdersToProvider extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:orders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cron for Order Status';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $orders = Order::where('status', OrderStatusEnum::PROCESSING->value)->get();

        foreach ($orders as $order) {
            /**
             * @var Order $order
             */

            $orderService = OrderService::build($order);

            dispatch(new SendSellerJob($orderService));
        }

        $this->info('status');
    }
}
