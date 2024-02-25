<?php

namespace App\Repositories;

use App\Models\Refer;
use Illuminate\Database\Eloquent\Collection;
use App\Models\User;

class ReferRepository extends BaseRepository
{
    public function model(): string
    {
        return Refer::class;
    }

    public function getReferrals(User $user): Collection|array
    {
        return $this->startConditions()::select('users.*')
            ->join('refer_user as r', 'refers.id', '=', 'r.refer_id')
            ->join('users', 'r.user_id', '=', 'users.id')
            ->where('refers.user_id', $user->id)
            ->distinct()
            ->get();
    }
}
