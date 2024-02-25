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
use App\Models\User;
use App\Models\UserTwo;
use App\Services\Balance;
use App\Services\OrderService;
use App\Services\SellerService;
use Illuminate\Console\Command;
use Ixudra\Curl\Facades\Curl;

class MoveUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:move';

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

        $users = UserTwo::all();
        foreach ($users as $item) {
            $userData = [
                'firstname' => $item->firstname,
                'lastname' => $item->lastname,
                'username' => $item->username,
                'language_id' => $item->language_id,
                'email' => $item->email,
                'balance' => $item->balance,
                'password' => $item->password,
            ];

            $user = new User($userData);

            $user->save();
        }

        $this->info('status');
    }
}
