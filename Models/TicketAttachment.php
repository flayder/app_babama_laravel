<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TicketAttachment extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    public function supportMessage()
    {
        return $this->belongsTo(TicketMessage::class, 'ticket_message_id');
    }
}
