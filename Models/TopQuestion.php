<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TopQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
    	'is_feedback',
        'question',
        'answer',
        'group_name'
    ];

    public $timestamps = false;
}
