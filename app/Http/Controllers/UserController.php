<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\User;
use Illuminate\Support\Facades\Hash;
use Validator;

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
            'password.confirmed' => '确认密码和原密码不一致',
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
        ]);
        Auth::login($user);

        return $this->resultSuccess(Auth::user());
    }

    public function logout()
    {
        Auth::logout();
    }

    public function test(Request $request)
    {
        $user = Auth::guard('api')->user();
        var_dump($user->email);
        exit;
        $name = $request->input("name");
        return "abc || " . $name;
    }
}
