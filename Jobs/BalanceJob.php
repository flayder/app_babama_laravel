<?php

namespace App\Jobs;

use App\Modules\Transaction\DTO\TransactionDto;
use App\Modules\Transaction\Facades\Transaction;
use App\Repositories\UserRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\BalanceService;
use App\Models\User;
use App\Enums\BalanceMethodsEnum;

class BalanceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected BalanceMethodsEnum $method;
    protected float $count;
    protected User $user;
    protected UserRepository $userRepository;
    protected ?TransactionDto $transactionDto;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user, BalanceMethodsEnum $method, float $count, TransactionDto $transactionDto = null)
    {
        $this->method = $method;
        $this->user = $user;
        $this->count = $count;
        $this->userRepository = app(UserRepository::class);
        $this->transactionDto = $transactionDto;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $userCurrent = $this->userRepository->getById($this->user->id);
        $balance = new BalanceService($userCurrent);

        switch ($this->method->value) {
            case BalanceMethodsEnum::ADD->value: {
                $transaction = $this->transactionDto ?: Transaction::addReplenishment($userCurrent, $this->count);
                $balance->add($this->count);
                Transaction::setSuccess($transaction);
                break;
            }

            case BalanceMethodsEnum::DEDUCT->value: {
                $balance->deduct($this->count);
                break;
            }

            case BalanceMethodsEnum::UPDATE->value: {
                $balance->change($this->count);
                break;
            }

            case BalanceMethodsEnum::RESET->value: {
                $balance->reset();
                break;
            }
        }
    }
}
