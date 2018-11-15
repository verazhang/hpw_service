<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\Income;
use App\Pay;
use App\WorkerSalary;
use App\Worker;
use DB;
use Redis;

class ReportController extends Controller
{
    const LIST_MAX = 5;
    const WORKER_LIST_MAX = 10;
    /**
     * 用户收支情况
     * @return array
     */
    public function userContact(Request $request)
    {
        $uid = Auth::id();
        $totalIn = Income::where("user_id", $uid)->sum("cash");
        $totalPay = Pay::where("user_id", $uid)->sum("cash");
        $balance = $totalIn - $totalPay;
        return $this->resultSuccess(compact("totalIn", "totalPay", "balance"));
    }

    public function wokerList(Request $request)
    {
        $page = $request->input('page', 0);
        $size = $request->input('size', self::WORKER_LIST_MAX);
        $keywords = $request->input('k', '');

        $uid = Auth::id();
        //total salary
        $worker = Worker::selectRaw("worker.id, worker.name, worker.phone, worker.salary, ifnull(sum(worker_salary.cash),0) as total_salary")
            ->leftJoin("worker_salary", function($join) use ($uid) {
                $join->on('worker.id','=','worker_salary.worker_id')
                    ->where('worker.user_id', $uid)
                    ->where('worker_salary.user_id', $uid);
            })
            ->groupBy("worker.id")
            ->groupBy("worker.name")
            ->groupBy("worker.phone")
            ->groupBy("worker.salary")
            ->orderBy(DB::raw("convert(`name` using gbk)"), "asc");

        if ($keywords) {
            $worker = $worker->where("name", "like", $keywords."%");
        }
        $result = $worker->offset($page * $size)
            ->limit($size)
            ->get();
        return $this->resultSuccess($result);
    }

    public function searchWorkerList(Request $request)
    {
//        $page = $request->input('page', 0);
//        $size = $request->input('size', self::WORKER_LIST_MAX);
        $keywords = $request->input('k', '');

        $uid = Auth::id();
        $key = Worker::CACHE_SORTED_KEY;
        //默认获取所有
        if (!$keywords) {
        $count = Redis::zcard($key);
////        ZRANGE key start stop [WITHSCORES]
//            $hashKeys = Redis::zrange($key, 0, $count);
        } else {
            $count = 20;
        }
        $keywords = Worker::strToPinYin($keywords);
        $res = Redis::command('zscan', [$key, 0, 'MATCH', $uid."|".$keywords."*", 'COUNT', $count]);
        $hashKeys = array_keys($res[1]);
        sort($hashKeys);

        $data = [];
        foreach ($hashKeys as $hk) {
            $worker = Redis::hget(Worker::CACHE_HASH_KEY, $hk);
            $data[] = json_decode($worker);
        }
        return $this->resultSuccess($data);
//        ZSCAN key cursor [MATCH pattern] [COUNT count]

//        $res = Redis::command('zrangebylex', [Worker::CACHE_SORTED_KEY, "[".$keywords, "[".$keywords, 'LIMIT', $page*$size, $size]);
//        dd($res);
//        $data = [];
//        if (!$res) {
//            return [];
//        }
//        $res = $res[1];
//        foreach ($res as $k => $w) {
//            $data[] = json_decode($w);
//        }
//        return $data;
    }

    /**
     * 工人收支情况
     * @param $worker_id
     * @return array
     */
    public function workerContact($worker_id)
    {
        $uid = Auth::id();
        $wsalary = WorkerSalary::selectRaw("type, ifnull(sum(cash), 0) total")
            ->where("worker_id", $worker_id)
            ->where("user_id", $uid)
            ->groupBy("type")
            ->get();
        return $this->resultSuccess($wsalary);
    }

    public function fundList(Request $request)
    {
        $page = $request->input('page', 0);
        $size = $request->input('size', self::LIST_MAX);
        $uid = Auth::id();
        $data = Income::where("user_id", $uid)
            ->offset($page*$size)->limit($size)
            ->get();
        return $this->resultSuccess($data);
    }

    public function payList(Request $request)
    {
        $page = $request->input('page', 0);
        $size = $request->input('size', self::LIST_MAX);
        $uid = Auth::id();
        $data = Worker::selectRaw("worker.*, worker_salary.cash")
            ->join("worker_salary", "worker.id", "=", "worker_salary.worker_id")
//        WorkerSalary::where("user_id", $uid)
            ->where("type", WorkerSalary::TYPE_PAID)
            ->where("worker.user_id", $uid)
            ->where("worker_salary.user_id", $uid)
            ->offset($page*$size)->limit($size)
            ->get();
        return $this->resultSuccess($data);
    }
}
