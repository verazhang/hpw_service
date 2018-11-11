<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Worker extends Model
{
    protected $table = 'worker';
    protected $fillable = [
        'name', 'phone', 'salary', 'started_at', 'ended_at', 'avatar'
    ];
}
