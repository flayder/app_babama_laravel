<?php

namespace App\Repositories;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Collection;
use App\Models\User;

class TransactionRepository extends BaseRepository
{
    public function model(): string
    {
        return Transaction::class;
    }

    public function getBalanced(Collection $users, $from_date = false, $to_date = false): Collection|array
    {
        $referrals = $this->startConditions()
                ::select('user_id')
                ->where('type', 'replenishment')
                ->where('status', 'success')
                ->whereIn('user_id',  $users->pluck('id'))
                ->distinct();
                
        if($from_date)
            $referrals->where('created_at', '>', $from_date);

        if($to_date)
            $referrals->where('created_at', '<', $to_date);
            
        return $referrals->get();
    }

    public function getTransactionsByPeriod(Collection $users, $from_date = false, $to_date = false): Collection|array
    {
        $referrals = $this->startConditions()
                ->whereIn('user_id', $users->pluck('id'))
                ->where('type', 'replenishment')
                ->where('status', 'success');
        if($from_date)
            $referrals->where('created_at', '>', $from_date);

        if($to_date)
            $referrals->where('created_at', '<', $to_date);

        return $referrals->get();
    }
}
