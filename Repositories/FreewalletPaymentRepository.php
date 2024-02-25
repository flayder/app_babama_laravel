<?php

namespace App\Repositories;

use App\Models\FreewalletPayment;
use Illuminate\Database\Eloquent\Collection;
use App\Models\User;

class FreewalletPaymentRepository extends BaseRepository
{
    public function model(): string
    {
        return FreewalletPayment::class;
    }

    public function getAll(): Collection|array
    {
        return $this->startConditions()::get();
    }

    public function getById(int $id): FreewalletPayment
    {
        return $this->startConditions()::findOrFail($id);
    }

    public function getTotalPrice(User $user, bool $isBalance = false)
    {
        if(!$isBalance)
            return $this->startConditions()::where('status', '!=', '9')->where('user_id', $user->id)->get();

        return $this->startConditions()::where('status', '9')->where('user_id', $user->id)->get();
    }

}
