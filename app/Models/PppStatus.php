<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PppStatus extends Model
{
    protected $table = 'ppp_statuses';

    protected $fillable = [
        'nome',
        'slug',
        'ordem',
    ];
}
