<?php

namespace App\Http\Controllers;

use App\Settings;
use App\WorkerSalary;
use Illuminate\Http\Request;
use Auth;
use App\Worker;
use Validator;
use Overtrue\Pinyin\Pinyin;
use Redis;

class WorkerController extends Controller
{
    const DROPDOWN_MAX = 10;
    const LIST_MAX = 5;
    const WORKER_CACHE_KEY = "workercache";


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

    public function search(Request $request)
    {
        $keywords = $request->input("k", "");

        //输入的是名称首字母
        if (!preg_match('/^[0-9A-Za-z]+$/', $keywords)) {
            $pinyin = new Pinyin();
            $res = $pinyin->name($keywords, PINYIN_KEEP_NUMBER_AND_ENGLISH);
            $keywords = $pinyin->abbr(implode(" ", $res), PINYIN_KEEP_NUMBER_AND_ENGLISH);
        }
//        dd($keywords);
        $uid = Auth::id();

        $fee = Settings::getSettings(Settings::KEY_MEALS);
        $data = $this->searchByCache($uid, $keywords);
        return $this->resultSuccess(['workers'=>$data, 'meals_fee'=>$fee]);
    }

    public function salaryList(Request $request, $worker_id)
    {
        $page = $request->input('page', 0);
        $size = $request->input('size', self::LIST_MAX);
        $offset = $page * $size;
        $uid = Auth::id();
        $list = WorkerSalary::where("worker_id", $worker_id)
            ->where("user_id", $uid)
            ->orderBy("created_at", "desc")
            ->offset($offset)->limit($size)
            ->get();
        return $this->resultSuccess($list);
    }

    public function storeWorker(Request $request)
    {
        $pinyin = new Pinyin();
        $key = self::WORKER_CACHE_KEY;
        Redis::del($key);
        $count = Worker::count();
        $limit = 50;
        for($i = 0; $i < $count; $i++) {
            $data = Worker::offset($i*$limit)->limit($limit)->get();
            foreach ($data as $w) {
                $res = $pinyin->name($w->name, PINYIN_KEEP_NUMBER_AND_ENGLISH);
                $namePinyin = $pinyin->abbr(implode(" ", $res), PINYIN_KEEP_NUMBER_AND_ENGLISH);
                // HSET KEY_NAME FIELD VALUE
                Redis::hset($key, $w->user_id."|".$namePinyin, json_encode($w->getAttributes()));
            }
            $i += $limit;
        }
    }

    /**
     * 从Redis缓存中搜索工人
     * @param $uid
     * @param string $keywords
     * @return array
     */
    protected function searchByCache($uid, $keywords = "")
    {
        //HSCAN key cursor [MATCH pattern] [COUNT count]
        $res = Redis::command('hscan', [self::WORKER_CACHE_KEY, 0, 'match', $uid."|".$keywords.'*', 'count', 10]);
        $data = [];
        if (!$res) {
            return [];
        }
        $res = $res[1];
        foreach ($res as $k => $w) {
            $data[] = json_decode($w);
        }
        return $data;
    }

    /**
     * 从数据库中搜索工人
     * @param $uid
     * @param string $keywords
     * @return mixed
     */
    protected function searchByDB($uid, $keywords = "")
    {
        $model = Worker::where("user_id", $uid);
        if ($keywords) {
            $data = $model->where("name", "like", $keywords."%")->orderBy("name", "asc")->get(["id", "name", "salary"]);
        } else {
            $data = $model->orderBy("name", "asc")->get(["id", "name", "salary"]);//->pluck("name", "id");
        }
        return $data;
    }
}
