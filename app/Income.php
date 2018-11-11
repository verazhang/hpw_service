<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Income extends Model
{
    protected $table = "income";
    protected $fillable = ["cash", "user_id"];
}
