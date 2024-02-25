<?php

namespace App\Modules\Transaction\Contracts;

use App\Dto\Order\OrderDto;
use App\Models\User;
use App\Modules\Transaction\DTO\TransactionDto;
use Illuminate\Support\Collection;

interface Transaction
{
    public function addReplenishment(User $user, float $amount, ?OrderDto $order): TransactionDto;

    public function addPayOrder(OrderDto $orderDto): TransactionDto;

    public function addOrder(OrderDto $orderDto): TransactionDto;

    public function get(int $id): TransactionDto;

    public function getByUuid(string $uuid): TransactionDto;

    public function getByUser(User $user): Collection;

    public function getByOrder(OrderDto $orderDto): Collection;

    public function setFailed(TransactionDto $transactionDto): void;

    public function setSuccess(TransactionDto $transactionDto): void;

    public function setDetail(TransactionDto $transactionDto, string $detail): void;
}
