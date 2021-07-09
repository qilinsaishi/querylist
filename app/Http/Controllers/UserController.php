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
            case "checkMobileExist"://检查手机号存在
                $return = $userService->checkMobileExist($data['params']['mobile']??"");
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
