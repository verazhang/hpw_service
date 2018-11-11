<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use Illuminate\Validation\Rule;
use App\Settings;

class SettingsController extends Controller
{
    public function get($key)
    {
        $validator = Validator::make(["key"=>$key], [
            "key" => ["required", Rule::in(Settings::KEYS)]//, Rule::exists("settings")
        ]);
        if ($validator->fails()) {
            return $this->resultFail($validator->errors());
        }

        $result = Settings::where("key", $key)->first();
        return $result ? $this->resultSuccess($result->value) : $this->resultFail("The key not found");
    }
}
