<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\Income;
use App\Pay;
use App\WorkerSalary;
use App\Worker;

class ReportController extends Controller
{
    const LIST_MAX = 5;
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

    public function wokerList()
    {
        $uid = Auth::id();
        //total salary
        $result = Worker::selectRaw("worker.id, worker.name, worker.phone, ifnull(sum(worker_salary.cash),0) as total")
            ->leftJoin("worker_salary", function($join) use ($uid) {
                $join->on('worker.id','=','worker_salary.worker_id')
                    ->where('worker.user_id', $uid)
                    ->where('worker_salary.user_id', $uid);
            })
            ->groupBy("worker.id")
            ->groupBy("worker.name")
            ->groupBy("worker.phone")
            ->orderBy("name", "asc")
            ->get();
        return $this->resultSuccess($result);
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
