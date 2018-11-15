<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class WorkerSalary extends Model
{
    const TYPE_SALARY = "salary";//工资
    const TYPE_MEALS = "meals";//饭费
    const TYPE_EXTRA= "extra";//加班或额外
    const TYPE_ADVANCE = "advance";//预支
    const TYPE_PAID = "paid";//已支付

    const TYPES = [
        self::TYPE_SALARY,
        self::TYPE_MEALS,
        self::TYPE_EXTRA,
        self::TYPE_ADVANCE,
        self::TYPE_PAID,
    ];
    protected $table = 'worker_salary';
    protected $fillable = [
        'worker_id', 'user_id', 'cash', 'type', 'record_at',
    ];

    public function createRecord($data, $dock = false) {
        extract($data);
        return WorkerSalary::create([
            "user_id" => $user_id,
            "worker_id" => $worker_id,
            "record_at" => $record_at,
            "cash" => $dock ? -1 * $cash : $cash,
        ]);
    }

//    public function setRecordAtAttribute($value)
//    {
//        $this->attributes['record_at'] = is_int($value) ? $value : strtotime($value);
//    }

    public function getRecordAtAttribute()
    {
        $carbon = Carbon::createFromTimeString($this->attributes['record_at']);
        return $carbon->toDateString();
    }
}
