<?php

namespace App\Repositories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;
use App\Models\User;

class OrderRepository extends BaseRepository
{
    public function model(): string
    {
        return Order::class;
    }

    public function getAll(): Collection|array
    {
        return $this->startConditions()::get();
    }

    public function getById(int $id): Order
    {
        return $this->startConditions()::findOrFail($id);
    }

    public function getByUser(User $user): Collection|array
    {
        return $this->startConditions()::where('user_id', $user->id)->get();
    }

    public function getReferralOrders(Collection $users, $from_date = false, $to_date = false, string $status = 'completed'): Collection|array
    {
        $referrals = $this->startConditions()
                ->where('status', $status)
                ->whereIn('user_id', $users->pluck('id'));
        if($from_date)
            $referrals->where('created_at', '>', $from_date);

        if($to_date)
            $referrals->where('created_at', '<', $to_date);

        return $referrals->get();
    }
}
