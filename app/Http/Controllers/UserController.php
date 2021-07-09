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
            case "sendRegSms":
                break;
            case "sendLoginSms":
                break;
            case "loginBySms":
                break;
            case "loginByUser":
                break;
        }
        return $return;

    }
}
