<?php

namespace App\Services;

use App\Helpers\Jwt;

class UserService
{
    public function test($arr = [])
    {
        $arr = ['a'=>1,'b'=>2,'c'=>3,'expire_time'=>3600];
        $token = (Jwt::getToken($arr));
        //echo "token:".$token;
        $info = (Jwt::getUserId($token));
        //echo "info:".$info;
        print_R($info);
        die();
    }
}
