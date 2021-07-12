<?php

namespace App\Http\Controllers;

use App\Libs\AjaxRequest;
use App\Libs\ClientServices;
use App\Services\Data\DataService;
use App\Services\UserService;
use Illuminate\Http\Request;


use QL\QueryList;
use GuzzleHttp\Client;


class UserController extends Controller
{

    public function index()
    {
        $data=$this->payload;
        $userService = new UserService();
        switch($data['type'])
        {
            case "checkMobileAvailable"://检查手机号可用（登陆/注册）
                $return = $userService->checkMobileAvailable($data['params']['mobile']??"",$data['params']['action']??"login");
                break;
            case "sendSmsCode"://发短信（登陆/注册）
                $return = $userService->sendSmsCode($data['params']['mobile']??"",$data['params']['action']??"login");
                break;
            case "loginBySms"://短信登陆
                $return = $userService->loginBySms($data['params']['mobile']??"",$data['params']['code']??"123456");
                break;
            case "regBySms"://短信注册
                $return = $userService->regBySms($data['params']['mobile']??"",$data['params']['code']??"123456");
                break;
            case "loginByUser":
                break;
        }
        return $return;

    }
}
