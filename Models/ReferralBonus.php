<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReferralBonus extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];
    protected $table = 'referral_bonuses';

    public function user()
    {
        return $this->belongsTo(User::class, 'from_user_id', 'id');
    }

    public function bonusBy()
    {
        return $this->belongsTo(User::class, 'to_user_id', 'id');
    }
}
