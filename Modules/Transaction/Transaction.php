<?php

namespace App\Modules\Transaction;

use App\Dto\Order\OrderDto;
use App\Models\User;
use App\Modules\Transaction\DTO\TransactionCreateDto;
use App\Modules\Transaction\DTO\TransactionDto;
use App\Modules\Transaction\Enums\TransactionStatusEnum;
use App\Modules\Transaction\Enums\TransactionTypeEnum;
use App\Modules\Transaction\Repositories\TransactionRepository;
use App\Modules\Transaction\Services\TransactionService;
use Illuminate\Support\Collection;

class Transaction implements Contracts\Transaction
{
    private Models\Transaction $transaction;
    private TransactionRepository $transactionRepository;

    public function __construct()
    {
        $this->transaction = app(Models\Transaction::class);
        $this->transactionRepository = app(TransactionRepository::class);
    }

    public function addReplenishment(User $user, float $amount, ?OrderDto $order = null): TransactionDto
    {
        $transactionCreateDto = new TransactionCreateDto(
            $user,
            [
                'type' => TransactionTypeEnum::REPLENISHMENT->value,
                'status' => TransactionStatusEnum::PENDING->value,
                'amount' => $amount,
            ],
            $order
        );

        $transaction = TransactionService::create($transactionCreateDto);

        return new TransactionDto($transaction);
    }

    public function addPayOrder(OrderDto $orderDto): TransactionDto
    {
        $transactionCreateDto = new TransactionCreateDto(
            $orderDto->user,
            [
                'type' => TransactionTypeEnum::PAY->value,
                'status' => TransactionStatusEnum::SUCCESS->value,
            ],
            $orderDto
        );

        $transaction = TransactionService::create($transactionCreateDto);

        return new TransactionDto($transaction);
    }

    public function addOrder(OrderDto $orderDto): TransactionDto
    {
        $transactionCreateDto = new TransactionCreateDto(
            $orderDto->user,
            [
                'type' => TransactionTypeEnum::ORDER->value,
                'status' => TransactionStatusEnum::SUCCESS->value,
            ],
            $orderDto
        );

        $transaction = TransactionService::create($transactionCreateDto);

        return new TransactionDto($transaction);
    }

    public function orderStatusChange(OrderDto $orderDto): TransactionDto
    {
        $transactionCreateDto = new TransactionCreateDto(
            $orderDto->user,
            [
                'type' => TransactionTypeEnum::ORDER_STATUS->value,
                'status' => TransactionStatusEnum::SUCCESS->value,
                'detail' => 'Статус заказа #' . $orderDto->id . ' изменен на "' . $orderDto->status->label() . '"'
            ],
            $orderDto
        );

        $transaction = TransactionService::create($transactionCreateDto);

        return new TransactionDto($transaction);
    }

    public function get(int $id): TransactionDto
    {
        $this->transaction = $this->transactionRepository->getById($id);

        return new TransactionDto($this->transaction);
    }

    public function getByUuid(string $uuid): TransactionDto
    {
        $this->transaction = $this->transactionRepository->getByUuid($uuid);

        return new TransactionDto($this->transaction);
    }

    public function getByOrder(OrderDto $orderDto): Collection
    {
        $transactions = $this->transactionRepository->getByOrder($orderDto->id);

        return $transactions->map(fn ($item) => new TransactionDto($item));

    }

    public function getByUser(User $user): Collection
    {
        $transactions = $this->transactionRepository->getByUser($user->id);

        return $transactions->map(fn ($item) => new TransactionDto($item));
    }

    public function setFailed(TransactionDto $transactionDto): void
    {
        $this->transaction = $this->transactionRepository->get($transactionDto->id);

        $this->transaction->status = TransactionStatusEnum::FAILED->value;
        $this->transaction->saveOrFail();
    }

    public function setSuccess(TransactionDto $transactionDto): void
    {
        $this->transaction = $this->transactionRepository->getById($transactionDto->id);

        $this->transaction->status = TransactionStatusEnum::SUCCESS->value;
        $this->transaction->saveOrFail();
    }

    public function setDetail(TransactionDto $transactionDto, string $detail): void
    {
        $this->transaction = $this->transactionRepository->get($transactionDto->id);

        $this->transaction->detail = $detail;
        $this->transaction->saveOrFail();
    }



}
