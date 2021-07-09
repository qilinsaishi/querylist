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
                    $return = ['result'=>($action=="login"?0:1),'msg'=>"手机号码尚未注册"];
                }
                else
                {
                    $return = ['result'=>($action=="login"?1:0),'msg'=>"手机号码已经注册"];
                }
            }
            else//不存在，查用户表
            {
                $userInfo = $this->userModel->getUserByMobile($mobile);
                //查到
                if(isset($userInfo['user_id']))
                {
                    //标记缓存
                    $this->user_redis->set($key,$userInfo['user_id'],86400);
                    $return = ['result'=>($action=="login"?1:0),'msg'=>"手机号码已经注册"];
                }
                else
                {
                    //标记缓存
                    $this->user_redis->set($key,0,60);
                    $return = ['result'=>($action=="login"?0:1),'msg'=>"手机号码尚未注册"];
                }
            }
        }
        return $return;
    }
    //发短信
    public function sendSmsCode($mobile,$action="login")
    {
        $cacheTime = 30*60;
        $resendTime = 10;
        $available = $this->checkMobileAvailable($mobile,$action);
        if($available['result'])
        {
            //检车手机号在缓存中存在
            $key = "mobile_".$action."_".$mobile;
            $exists = $this->user_redis->exists($key);
            //有发送记录
            if($exists)
            {
                $cache = $this->user_redis->get($key);
                $cache = json_decode($cache,true);
                $timeLag = time()-$cache['send_time'];
                //当前时间与发送时间的时间差超过最小等待时间
                if($timeLag>$resendTime)
                {
                    //发新的
                    $this->user_redis->del($key);
                    $return = $this->sendSmsCode($mobile,$action);
                }
                else
                {
                    $return = ['result'=>1,'msg'=>"短信已发送,".intval($cacheTime/60)."分钟内有效，请注意查收,剩余等待时间为：".($resendTime-$timeLag)."秒"];
                }
            }
            else
            {
                //生成验证码
                $code = sprintf("%06d",rand(0,999999));
                //发动短信
                $sendSms = true;
                //$sendSms = (new AliyunService())->sms($mobile,"common",$params = ["code"=>$code]);
                if($sendSms)
                {
                    //标记缓存
                    $this->user_redis->set($key,json_encode(['code'=>$code,'send_time'=>time()]),$cacheTime);
                    $return = ['result'=>1,'msg'=>"短信已发送,".intval($cacheTime/60)."分钟内有效，请注意查收"];
                }
                else
                {
                    $return = ['result'=>1,'msg'=>"短信发送失败，请稍后再试，请注意查收"];
                }
            }
        }
        else
        {
            $return = $available;
        }
        return $return;

    }
    //以用户ID为依据，更新用户信息
    public function updateUserByUser($user_id,$userInfo)
    {

    }
    //短信登陆
    public function loginBySms($mobile,$code)
    {
        //检查手机号是否可用
        $available = $this->checkMobileAvailable($mobile,"login");
        if($available[['result']])
        {

        }
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
