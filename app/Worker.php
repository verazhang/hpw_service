<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Overtrue\Pinyin\Pinyin;

class Worker extends Model
{
    protected $table = 'worker';
    protected $fillable = [
        'name', 'phone', 'salary', 'user_id', 'started_at', 'ended_at', 'avatar'
    ];
    const CACHE_HASH_KEY = "workerhash";
    const CACHE_SORTED_KEY = "workersorted";

    protected function strToPinYin($keywords)
    {
        //输入的是名称首字母
        if (!preg_match('/^[0-9A-Za-z]+$/', $keywords)) {
            $pinyin = new Pinyin();
            $res = $pinyin->name($keywords, PINYIN_KEEP_NUMBER_AND_ENGLISH);
            $keywords = $pinyin->abbr(implode(" ", $res), PINYIN_KEEP_NUMBER_AND_ENGLISH);
        }
        return $keywords;
    }
    public function salaries()
    {
        return $this->hasMany('App\WorkerSalary');
    }
}
