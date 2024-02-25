<?php

namespace App\Jobs;

use App\Enums\BalanceMethodsEnum;
use App\Enums\OrderStatusEnum;
use App\Helper\SellerHelper;
use App\Models\ApiProvider;
use App\Models\User;
use App\Modules\Transaction\Facades\Transaction;
use App\Repositories\UserRepository;
use App\Services\ApiProviderService;
use App\Services\Balance;
use App\Services\BalanceService;
use App\Services\SellerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Support\Facades\Log;

class PayOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected OrderService $order;

    protected User $user;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(OrderService $order, User $user)
    {
        $this->order = $order;
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $userActual = app(UserRepository::class)->getById($this->user->id);

        $orderService = $this->order;
        $balance = new Balance($userActual);

        Log::info('[Pay order Job] status]', [
            $orderService->show()->status->value
        ]);

        if ($orderService->show()->status->value != OrderStatusEnum::UNPAID->value) {
            return;
        }

        Log::info('[Pay order Job] balance && price]', [
            $balance->get(),
            $orderService->getOrderPrice()
        ]);

        if ($balance->get() < $orderService->getOrderPrice()) {
            return;
        }

        $orderDto = $orderService->pay();

        $balance->deduct($orderService->getOrderPrice());

        Transaction::addPayOrder($orderDto);
        Transaction::orderStatusChange($orderDto);

        SendSellerJob::dispatch($orderService);

    }
}
