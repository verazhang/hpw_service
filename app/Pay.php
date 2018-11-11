<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Pay extends Model
{
    protected $table = "pay";
    protected $fillable = ["cash", "user_id", "worker_id"];
}
