<?php

namespace App\Http\Controllers;

use App\Settings;
use Illuminate\Http\Request;
use Auth;
use App\Worker;
use Validator;
use Overtrue\Pinyin\Pinyin;

class WorkerController extends Controller
{
    const DROPDOWN_MAX = 10;
    const LIST_MAX = 20;
    protected $uid;
    public function __construct()
    {
        $this->uid = Auth::id();
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
        $data["user_id"] = $this->uid;
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

    public function search(Request $request)
    {
        $name = $request->input("name", "");
//        $pinyin = new Pinyin();
//        $res = $pinyin->name('单田芳');
//        $res = $pinyin->abbr(implode(" ", $res), PINYIN_KEEP_ENGLISH);
////        $res = $pinyin->name('鞠婧祎');
//        return $res;
        $uid = Auth::id();
        $model = Worker::where("user_id", $uid);
        if ($name) {
            $data = $model->where("name", "like", $name."%")->orderBy("name", "asc")->get(["id", "name", "salary"]);
            return $this->resultSuccess($data);
        }
        $data = $model->orderBy("name", "asc")->get(["id", "name", "salary"]);//->pluck("name", "id");
        $fee = Settings::getSettings(Settings::KEY_MEALS);
        return $this->resultSuccess(['workers'=>$data, 'meals_fee'=>$fee]);
    }

//    public function updateCache()
//    {
//
//    }
}
