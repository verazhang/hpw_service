<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Settings extends Model
{
    const KEY_MEALS = "meals";
    const KEY_EXTRA = "extra";
    const KEYS = [
        self::KEY_MEALS, self::KEY_EXTRA];
    protected $table = "settings";
    protected $fillable = [
        "key", "value"
    ];

    protected function getSettings($key)
    {
        $fee = $this->where("key", $key)->first();
        return $fee ? $fee->value : 0;
    }
}
