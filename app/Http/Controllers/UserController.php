<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\User;
use App\WorkerSalary;
use App\Settings;
use App\Income;
use App\Pay;
use Illuminate\Support\Facades\Hash;
use Validator;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Redis;

class UserController extends Controller
{
    public function login(Request $request)
    {
        $data = $request->all();
        $messages = [
            'name.required' => '请输入用户名',
            'password.required' => '请输入密码',
        ];
        $validator = Validator::make($data, [
            'name' => 'required|string',
            'password' => 'required|string',
        ], $messages);

        if ($validator->fails()) {
            return $this->resultFail($validator->errors());
        }
        $name = $data['name'] ?? "";
        $password = $data['password'] ?? "";
        if (Auth::attempt(['name' => $name, 'password' => $password])) {
//            $this->session->put($this->getName(), $id);
//            $request->session()->put('user:profile', $name." ses ".$password);
//            Redis::set('user:profile', $name." ".$password);
            $user = Auth::guard()->user();
            $user->generateToken();
            return $this->resultSuccess(Auth::user());
        }
        return $this->resultFail("用户名或密码不正确");
    }

    /**
     * @param Request $request name email password password_confirmation _token
     * @return mixed
     */
    public function register(Request $request)
    {
        $data = $request->all();
        $messages = [
            'name.required' => '请输入用户名',
            'name.unique' => '用户名已存在，请输入其他名字',
            'password.required' => '请输入密码',
//            'password.confirmed' => '确认密码和原密码不一致',
            'email.required' => '请输入邮箱地址',
            'email.unique' => '邮箱地址已存在，请输入真实邮箱，之后用以找回密码',
        ];
        $validator = Validator::make($data, [
            'name' => ['required', 'string', 'max:255', 'unique:users'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], $messages);

        if ($validator->fails()) {
            return $this->resultFail($validator->errors());
        }
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'phone' => $data['phone'],
        ]);
        Auth::login($user);
        $user->generateToken();

        return $this->resultSuccess($user);
    }

    public function logout()
    {
        Auth::logout();
    }

    public function get()
    {
        $user = Auth::user();
        $fee = Settings::getSettings(Settings::KEY_MEALS);
        $user->meals_fee = $fee;
        return $this->resultSuccess($user);
    }

    /**
     * 为工人记录工资，扣除饭费，预支工资
     * @param Request $request
     * @return array
     */
    public function addSalary(Request $request)
    {
        $data = $request->all();
        $validator = Validator::make($data, [
            "worker_id" => "required",
            "cash" => "required|numeric",
            "meals" => "numeric",
            "type" => Rule::in(WorkerSalary::TYPES),
        ]);
        if ($validator->fails()) {
            return $this->resultFail($validator->errors());
        }
        extract($data);

        $record_at = $record_at ?? Carbon::now();

        if ($type  == WorkerSalary::TYPE_ADVANCE) {
            $cash = -1 * $cash;
        }

        $user_id = Auth::id();
        //扣除生活费
        if (isset($meals) && $meals) {
            $ws = WorkerSalary::create([
                "user_id" => $user_id,
                "worker_id" => $worker_id,
                "record_at" => $record_at,
                "cash" => -1 * $meals,
                "type" => WorkerSalary::TYPE_MEALS,
            ]);
            if (!$ws) {
                return $this->resultFail("Add meals fee failed");
            }
        }

        WorkerSalary::create([
            "user_id" => $user_id,
            "worker_id" => $worker_id,
            "record_at" => $record_at,
            "cash" => $cash,
            "type" => $type,
        ]);

        return $this->resultSuccess();
    }

    /**
     * 拨款
     */
    public function allocateFund(Request $request)
    {
        $data = $request->all();
        $validator = Validator::make($data, [
            "cash" => "required"
        ]);
        if ($validator->fails()) {
            return $this->resultFail($validator->errors());
        }
        return $this->resultSuccess(
            Income::create([
                "cash" => $data["cash"],
                "user_id" => Auth::id(),
            ])
        );
    }
    /**
     * 发工资
     */
    public function payCash(Request $request)
    {
        $data = $request->all();
        $validator = Validator::make($data, [
            "cash" => "required",
            "worker_id" => "required",
        ]);
        if ($validator->fails()) {
            return $this->resultFail($validator->errors());
        }
        return $this->resultSuccess(Pay::create([
            "cash" => $data["cash"],
            "worker_id" => $data["worker_id"],
            "user_id" => Auth::id(),
        ]));
    }
}
