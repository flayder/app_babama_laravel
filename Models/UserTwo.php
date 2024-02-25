<?php

declare(strict_types=1);

namespace App\Models;

use App\Http\Traits\Notify;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property string email
 * @property string firstname
 * @property string password
 */
class UserTwo extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens;
    use Notifiable;
    use Notify;

    protected $table = 'users';

    protected $connection = 'mysql2';

    protected $fillable = [

    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public $allusers = [];

    protected $appends = ['fullname', 'mobile', 'profileName', 'photo'];

    protected $dates = ['sent_at'];

    public function getFullnameAttribute()
    {
        return $this->firstname.' '.$this->lastname;
    }

    public function getPhotoAttribute()
    {
        return getFile(config('location.user.path').$this->image);
    }

    public function getProfileNameAttribute()
    {
        return '@'.$this->username;
    }

    public function getMobileAttribute()
    {
        return $this->phone;
    }

    public function funds()
    {
        return $this->hasMany(Fund::class)->latest()->where('status', '!=', 0);
    }

    public function orders(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Order::class)->latest();
    }

    public function transaction()
    {
        return $this->hasOne(Transaction::class)->latest();
    }

    public function ticket()
    {
        return $this->hasMany(Ticket::class);
    }

    public function serviceRates()
    {
        return $this->hasMany(UserServiceRate::class, 'user_id')->latest();
    }

    public function siteNotificational()
    {
        return $this->morphOne(SiteNotification::class, 'siteNotificational', 'site_notificational_type', 'site_notificational_id');
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->mail($this, 'PASSWORD_RESET', $params = [
            'message' => '<a href="'.url('password/reset', $token).'?email='.$this->email.'" target="_blank">Click To Reset Password</a>',
        ]);
    }

    public function getReferralLinkAttribute()
    {
        return $this->referral_link = route('register', ['ref' => $this->username]);
    }

    public function referral()
    {
        return $this->belongsTo(self::class, 'referral_id');
    }

    public function referralBonusLog()
    {
        return $this->hasMany(ReferralBonus::class, 'from_user_id', 'id');
    }

    public function scopeLevel()
    {
        $count = 0;
        $user_id = $this->id;
        while (null != $user_id) {
            $user = self::where('referral_id', $user_id)->first();
            if (!$user) {
                break;
            } else {
                $user_id = $user->id;
                ++$count;
            }
        }

        return $count;
    }

    public function referralUsers($id, $currentLevel = 1)
    {
        $users = $this->getUsers($id);
        if ($users['status']) {
            $this->allusers[$currentLevel] = $users['user'];
            ++$currentLevel;
            $this->referralUsers($users['ids'], $currentLevel);
        }

        return $this->allusers;
    }

    public function getUsers($id)
    {
        if (isset($id)) {
            $data['user'] = self::whereIn('referral_id', $id)->get(['id', 'firstname', 'lastname', 'username', 'email', 'phone_code', 'phone', 'referral_id', 'created_at']);
            if (\count($data['user']) > 0) {
                $data['status'] = true;
                $data['ids'] = $data['user']->pluck('id');

                return $data;
            }
        }
        $data['status'] = false;

        return $data;
    }
}
