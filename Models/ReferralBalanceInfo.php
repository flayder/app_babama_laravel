<?php

namespace App\Models;

use App\Models\User;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferralBalanceInfo extends Model
{
    use HasFactory;

    public function user()
    {
    	return $this->belongsTo(User::class);
    }

    public function referral()
    {
    	return $this->belongsTo(User::class);
    }

    public function transaction()
    {
    	return $this->belongsTo(Transaction::class);
    }
}
