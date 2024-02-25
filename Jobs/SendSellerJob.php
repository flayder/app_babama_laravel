<?php

namespace App\Jobs;

use App\Dto\Seller\SellerFailedResponseDto;
use App\Dto\Seller\Send\SendSellerSuccessResponseDto;
use App\Enums\BalanceMethodsEnum;
use App\Helper\SellerHelper;
use App\Models\ApiProvider;
use App\Models\Order;
use App\Repositories\OrderRepository;
use App\Modules\Transaction\Facades\Transaction;
use App\Services\Balance;
use App\Services\OrderService;
use App\Services\SellerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Enums\OrderStatusEnum;

class SendSellerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected OrderService $order;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(OrderService $order)
    {
        $this->order = $order;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $orderDto = $this->order->show();
        $seller = ApiProvider::find($orderDto->service->api_provider_id);

        $sellerServiceInterface = SellerHelper::getSellerClass($seller);
        $seller = new SellerService($sellerServiceInterface);

        $orderService = $this->order;
        $orderDto = $orderService->show();

        $status = $orderService->send($seller);

        if ($status instanceof SendSellerSuccessResponseDto) {
            $orderDto = $orderService->changeStatus(OrderStatusEnum::IN_PROGRESS);

            Transaction::orderStatusChange($orderDto);
        } else if ($status instanceof SellerFailedResponseDto) {
            $balance = new Balance($orderDto->user);
            $balance->add($orderDto->price);

            $orderDto = $orderService->changeStatus(OrderStatusEnum::CANCELED);

            Transaction::orderStatusChange($orderDto);
        }
    }
}
