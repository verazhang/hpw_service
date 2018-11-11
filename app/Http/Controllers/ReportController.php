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
    /**
     * 用户收支情况
     * @return array
     */
    public function userContact()
    {
        $user_id = Auth::id();

        return $this->resultSuccess($user_id,Auth::guard('api')->user());

        $totalIn = Income::where("user_id", $user_id)->sum("cash");
        $totalPay = Pay::where("user_id", $user_id)->sum("cash");
        $balance = $totalIn - $totalPay;
        return $this->resultSuccess(compact("totalIn", "totalPay", "balance"));
    }

    public function wokerList()
    {
        //total salary
        $result = Worker::selectRaw("worker.*, ifnull(sum(cash),0) as total")
            ->leftJoin("worker_salary", "worker.id", "=", "worker_salary.worker_id")
            ->groupBy("worker.id")
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
        $wsalary = WorkerSalary::selectRaw("type, ifnull(sum(cash), 0) total")
            ->where("worker_id", $worker_id)
            ->groupBy("type")
            ->get();
        return $this->resultSuccess($wsalary);
    }
}
