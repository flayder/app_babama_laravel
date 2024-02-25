<?php

namespace App\Modules\Transaction\Repositories;

use App\Models\Order;
use App\Modules\Transaction\Models\Transaction;
use App\Repositories\BaseRepository;
use Illuminate\Support\Collection;

class TransactionRepository extends BaseRepository
{
    public function model(): string
    {
        return Transaction::class;
    }

    public function getAll(): Collection|array
    {
        return $this->startConditions()::get();
    }

    public function getById(int $id): Transaction
    {
        return $this->startConditions()::findOrFail($id);
    }

    public function getByUuid(string $uuid): Transaction
    {
        return $this->startConditions()::where('uuid', $uuid)->firstOrFail();
    }

    public function getByOrder(int $id): Collection
    {
        return $this->startConditions()::where('order_id', $id)->orderBy('id', 'desc')->get();
    }

    public function getByUser(int $id): Collection
    {
        return $this->startConditions()::where('user_id', $id)->orderBy('id', 'desc')->get();
    }
}
