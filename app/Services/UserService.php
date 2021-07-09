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
    public function checkMobileExist($mobile)
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
                echo "key_found;";die();
                $cache = $this->user_redis->get($key);
                print_R($cache);
                die();
            }
            else//不存在，查用户表
            {
                echo "key_not_found;";
                $userInfo = $this->userModel->getUserByMobile($mobile);
                //查到
                if(isset($userInfo['user_id']))
                {
                    $this->user_redis->set($key,$userInfo['user_id'],86400);
                    $return = ['result'=>1,'msg'=>"手机号码已经注册"];
                }
                else
                {
                    $this->user_redis->set($key,0,60);
                    $return = ['result'=>1,'msg'=>"手机号码尚未注册"];
                }
                echo "888";die();
            }
            echo "777";
            //print_R($cache);
            die();
            $return = ['result'=>1,'msg'=>"成功"];
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
