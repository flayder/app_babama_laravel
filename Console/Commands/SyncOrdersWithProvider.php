<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Dto\Order\OrderDto;
use App\Dto\Seller\OrderStatus\OrderStatusSellerSuccessResponseDto;
use App\Dto\Seller\SellerFailedResponseDto;
use App\Enums\OrderStatusEnum;
use App\Helper\SellerHelper;
use App\Jobs\RefundOrder;
use App\Models\Order;
use App\Services\Balance;
use App\Services\SellerService;
use Illuminate\Console\Command;
use Ixudra\Curl\Facades\Curl;

class SyncOrdersWithProvider extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:orders';

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
        Order::with(['service', 'service.provider'])
            ->whereNotIn('status', [OrderStatusEnum::COMPLETED->value, OrderStatusEnum::REFUNDED->value, OrderStatusEnum::CANCELED->value])->whereHas('service')->get()->map(function ($order): void {
            $service = $order->service;
                try {
                    if (!(isset($service->api_provider_id) && $service->provider)) {
                        return;
                    }

                    $apiProvider = ($service->provider);
                    $seller = SellerHelper::getSellerClass($apiProvider);
                    $sellerService = new SellerService($seller);
                    $orderDto = new OrderDto($order);

                    if (!$orderDto->apiOrderId){
                        return;
                    }

                    $status = $sellerService->getOrderStatus($orderDto);
                    dump($status);
                    if (($status instanceof OrderStatusSellerSuccessResponseDto)) {
                        $order->status_description = "Order: #{$status?->orderId}";
                        $order->api_order_id = $status?->orderId;
                    } elseif($status instanceof SellerFailedResponseDto) {
                        $order->status_description = "error:" . (string)$status?->error;
                    }

                    $order->start_counter = $status->startCount;
                    $order->remains = $status->remains;
                    $order->status = OrderStatusEnum::tryFrom(strtolower($status->status))->value;

                    if ($order->status == OrderStatusEnum::CANCELED->value) {
                        $user = $orderDto->user;
                        $balance = new Balance($user);
                        $balance->add($orderDto->price);
                    }
                    $order->save();


                } catch (\Throwable $exception) {

                }

        });

        $this->info('status');
    }
}
