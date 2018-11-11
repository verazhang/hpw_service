<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\Worker;
use Validator;

class WorkerController extends Controller
{
    const DROPDOWN_MAX = 10;
    const LIST_MAX = 20;
    protected $user_id;
    public function __construct()
    {
        $this->user_id = Auth::id();
    }

    public function register(Request $request)
    {
        $data = $request->all();
        $messages = [
            'name.required' => '请输入工人名称',
            'name.unique' => '工人名称已存在，请增加别名以区分',
            'phone.required' => '请输入电话号码',
            'phone.numeric' => '请输入正确的电话号码',
            'salary.numeric' => '请输入正确的工资',
        ];
        $validator = Validator::make($data, [
            'name' => 'required|string|unique:worker',
            'phone' => 'required|numeric',
            'salary' => 'numeric'
        ], $messages);
        if ($validator->fails()) {
            return $this->resultFail($validator->errors());
        }
        $data['salary'] = $data['salary'] ?? 0.00;
        $data["user_id"] = Auth::id();
        $worker = Worker::create($data);

        return $this->resultSuccess($worker);
    }

    public function get($id)
    {
        $worker = Worker::find($id);
        return $this->resultSuccess($worker);
    }

    public function searchSimple($name = "")
    {
        if ($name) {
            $data = Worker::where("name", "like", $name."%")->limit(self::DROPDOWN_MAX)->get(["id", "name"]);
            return $this->resultSuccess($data);
        }
        $data = Worker::limit(self::DROPDOWN_MAX)->get(["id", "name"]);
        return $this->resultSuccess($data);
    }

    public function search($name = "")
    {
        if ($name) {
            $data = Worker::where("user_id", $this->user_id)->where("name", "like", $name."%")->orderBy("name", "asc")->get();
            return $this->resultSuccess($data);
        }
        $data = Worker::orderBy("name", "asc")->pluck("name", "id");
        return $this->resultSuccess($data);
    }
}
