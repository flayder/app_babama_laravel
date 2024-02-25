<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\FreewalletCurrency;
use App\Models\FreewalletPaymentSystem;
use App\Models\User;

class FreewalletPayment extends Model
{
    use HasFactory;

    protected $fillable = [
    	'user_id',
    	'amount',
    	'account',
    	'description',
    	'status',
    	'payment_system_id',
    	'currency_id'
    ];

    public function currency()
    {
    	return $this->belongsTo(FreewalletCurrency::class);
    }

    public function payment_system()
    {
    	return $this->belongsTo(FreewalletPaymentSystem::class);
    }

    public function user()
    {
    	return $this->belongsTo(User::class);
    }
}
