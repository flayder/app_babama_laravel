<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Collection;
use App\Models\User;

class UserRepository extends BaseRepository
{
    public function model(): string
    {
        return User::class;
    }

    public function getAll(): Collection|array
    {
        return $this->startConditions()::get();
    }

    public function getById(int $id): ?User
    {
        return $this->startConditions()::findOrFail($id);
    }

    public function getByEmail(string $email): ?User
    {
        return $this->startConditions()::where('email', $email)->first();
    }

    public function getUsersByPeriod(Collection $users, $from_date = false, $to_date = false): Collection|array
    {
        $referrals = $this->startConditions()
                ->whereIn('id', $users->pluck('id'));
        if($from_date)
            $referrals->where('created_at', '>', $from_date);

        if($to_date)
            $referrals->where('created_at', '<', $to_date);

        return $referrals->get();
    }

}
