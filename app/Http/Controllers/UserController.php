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
        {
            $data=$this->payload;
            switch($data['type'])
            {
                case "checkMobileExist":
                    $return = [];
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
}
