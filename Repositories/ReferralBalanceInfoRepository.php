<?php

namespace App\Repositories;

use App\Models\ReferralBalanceInfo;
use Illuminate\Database\Eloquent\Collection;
use App\Models\User;

class ReferralBalanceInfoRepository extends BaseRepository
{
    public function model(): string
    {
        return ReferralBalanceInfo::class;
    }

    public function getBalanced(Collection $users, $from_date = false, $to_date = false): Collection|array
    {
        $referrals = $this->startConditions()
                ::select('user_id')
                ->whereIn('user_id', $users->pluck('id'))
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
                ::whereIn('user_id', $users->pluck('id'));
        if($from_date)
            $referrals->where('created_at', '>', $from_date);

        if($to_date)
            $referrals->where('created_at', '<', $to_date);

        return $referrals->get();
    }
}
