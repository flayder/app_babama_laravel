<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Ticket extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = ['id'];

    protected $casts =[
        'last_reply' => 'date'
    ];

    public function getUsernameAttribute()
    {
        return $this->name;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function messages()
    {
        return $this->hasMany(TicketMessage::class)->latest();
    }

    public function lastReply()
    {
        return $this->hasOne(TicketMessage::class)->latest();
    }

    public function getLastMessageAttribute()
    {
        return Str::limit($this->lastReply->message, 40);
    }
}
