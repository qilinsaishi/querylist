<?php

namespace App\Services;

use App\Libs\AjaxRequest;
use App\Libs\ClientServices;
use App\Services\Data\RedisService;
use QL\QueryList;

class UserService
{
    //检查手机号码是否已经注册
    public function checkMobileExist($mobile)
    {

    }
    //以用户ID为依据，更新用户信息
    public function updateUserByUser($user_id,$userInfo)
    {

    }
    //发送注册用的短信验证码
    public function sendRegSMS($mobile)
    {

    }
    //发送登陆用的短信验证码
    public function sendLoginSms($mobile)
    {

    }
    //短信登陆
    public function loginBySms($mobile,$code)
    {

    }
    //密码登陆
    public function loginByUser($mobile,$password)
    {

    }
    //短信注册
    public function regBySms($mobile,$code)
    {

    }
    //用户名注册
    public function regByUser($username,$password,$password_repeat)
    {

    }






}
