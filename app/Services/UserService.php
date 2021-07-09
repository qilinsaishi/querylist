<?php

namespace App\Services;

use App\Libs\AjaxRequest;
use App\Libs\ClientServices;
use App\Models\User\UserModel;
use Illuminate\Support\Facades\Redis;
use QL\QueryList;

class UserService
{
    public function __construct()
    {
        $this->user_redis = Redis::connection('user_redis');
        $this->userModel = new UserModel();
    }
    //检查手机号码是否已经注册
    public function checkMobileAvailable($mobile,$action="login")
    {
        //检查手机号是否有效
        $checkMobile = checkMobile($mobile);
        if(!$checkMobile)
        {
            $return = ['result'=>0,'msg'=>"手机号码不合法，请重新输入"];
        }
        else
        {
            //检车手机号在缓存中存在
            $key = "mobile_".$mobile;
            $exists = $this->user_redis->exists($key);
            if($exists)
            {
                $cache = $this->user_redis->get($key);
                if($cache==0)
                {
                    $return = ['result'=>$action=="login"?0:1,'msg'=>"手机号码尚未注册"];
                }
                else
                {
                    $return = ['result'=>$action=="login"?1:0,'msg'=>"手机号码已经注册"];
                }
            }
            else//不存在，查用户表
            {
                $userInfo = $this->userModel->getUserByMobile($mobile);
                //查到
                if(isset($userInfo['user_id']))
                {
                    $this->user_redis->set($key,$userInfo['user_id'],86400);
                    $return = ['result'=>$action=="login"?1:0,'msg'=>"手机号码已经注册"];
                }
                else
                {
                    $this->user_redis->set($key,0,60);
                    $return = ['result'=>$action=="login"?0:1,'msg'=>"手机号码尚未注册"];
                }
            }
        }
        return $return;
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
