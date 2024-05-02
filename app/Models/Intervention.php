<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Intervention extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'formation_id',
        'date',
        'time',
        'duration',
        'hourly_price_ht',
        'comment',
    ];
}
