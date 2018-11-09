<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    const CODE_FAIL = 0;
    const CODE_SUCCESS = 1;
    public function resultSuccess($data, $messages = [])
    {
        return [
            'code'=>self::CODE_SUCCESS,
            'data'=>$data,
            'error'=>$messages,
            ];
    }

    public function resultFail($messages = [], $data = [])
    {
        return [
            'code'=>self::CODE_FAIL,
            'data'=>$data,
            'error'=>$messages,
        ];
    }
}
