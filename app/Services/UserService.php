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
            $mobileUserCache = $this->getMobileUserCache($mobile);
            if($mobileUserCache>0)
            {
                $return = ['result'=>($action=="login"?0:1),'msg'=>"手机号码已经注册"];
            }
            elseif($mobileUserCache==0)
            {
                $return = ['result'=>($action=="login"?1:0),'msg'=>"手机号码尚未注册"];
            }
            else
            {
                $userInfo = $this->userModel->getUserByMobile($mobile);
                //查到
                if(isset($userInfo['user_id']))
                {
                    //标记缓存
                    $this->setMobileUserCache($mobile,$userInfo['user_id']);
                    $return = ['result'=>($action=="login"?1:0),'msg'=>"手机号码已经注册"];
                }
                else
                {
                    //标记缓存
                    $this->setMobileUserCache($mobile,0);
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
            $code = $this->getSmsCode($mobile,$action);
            //有获取到
            if($code['result']==1)
            {
                if(isset($code['wait']))
                {
                    $return = ['result'=>1,'msg'=>"短信已发送,".intval($cacheTime/60)."分钟内有效，请注意查收,剩余等待时间为：".$code['wait']."秒"];
                }
                else
                {
                    //发新的
                    $this->deleteSmsRedisKey($mobile,$action);
                    $return = $this->sendSmsCode($mobile,$action);
                }
            }
            else
            {
                //生成验证码
                $code2Send = sprintf("%06d",rand(0,999999));
                //发动短信
                $sendSms = true;
                //$sendSms = (new AliyunService())->sms($mobile,"common",$params = ["code"=>$code]);
                if($sendSms)
                {
                    //标记缓存
                    $this->user_redis->set($code['keyIfExist'],json_encode(['code'=>$code2Send,'send_time'=>time()]));
                    $this->user_redis->expire($code['keyIfExist'], $cacheTime);
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
        if($available['result'])
        {

        }
        else
        {
            $return = $available;
        }
        return $return;
    }
    //密码登陆
    public function loginByUser($mobile,$password)
    {

    }
    //短信注册
    public function regBySms($mobile,$code)
    {
        //检查手机号是否可用
        $available = $this->checkMobileAvailable($mobile,"reg");
        //可用
        if($available['result'])
        {
            //获取缓存中的验证码记录
            $currentCode = $this->getSmsCode($mobile,"reg");
            if($currentCode['result'])
            {
                //验证码正确
                if($currentCode['code']==trim($code))
                {
                    //尝试注册用户
                    $reg = $this->reg(["mobile"=>$mobile,"reg_type"=>1]);
                    if($reg['result']>0)
                    {
                        $this->deleteSmsRedisKey($mobile,"reg");
                        $return = ['result'=>1,"msg"=>"注册成功","user_id"=>$reg['user_id']];
                    }
                    else
                    {
                        $return = ['result'=>0,"msg"=>"注册失败"];
                    }
                }
                else
                {
                    $return = ['result'=>0,"msg"=>"验证码有误"];
                }
            }
            else
            {
                $return = ['result'=>0,"msg"=>"验证码尚未发送"];
            }
        }
        else
        {
            $return = $available;
        }
        return $return;
    }
    //用户名注册
    public function regByUser($username,$password,$password_repeat)
    {

    }
    //获取缓存中的验证码发送记录
    public function getSmsCode($mobile,$action="login")
    {
        $resendTime = 10;
        $currentTime = time();
        //检车手机号在缓存中存在
        $key = "mobile_".$action."_".$mobile;
        $exists = $this->user_redis->exists($key);
        if($exists)
        {
            $cache = $this->user_redis->get($key);
            $cache = json_decode($cache,true);
            $timeLag = $currentTime-$cache['send_time'];
            //如果时间差大于最小等待时间 正常返回
            if($timeLag>$resendTime)
            {
                $return = ["result"=>1,"code"=>$cache['code']];
            }
            else//返回验证码 标记需要继续等待
            {
                $return = ["result"=>1,"code"=>$cache['code'],"wait"=>$resendTime-$timeLag];
            }
        }
        else
        {
            $return = ["result"=>0,"keyIfExist"=>$key];
        }
        return $return;
    }
    //删除缓存中的短信发送记录
    public function deleteSmsRedisKey($mobile,$action)
    {
        $key = "mobile_".$action."_".$mobile;
        $this->user_redis->del($key);
        return true;
    }
    //注册用户
    public function reg($userInfo)
    {
        $reg = $this->userModel->insertUser($userInfo);
        if($reg>0)
        {
            $return = ["result"=>1,"user_id"=>$reg];
        }
        else
        {
            $return = ["result"=>0,"user_id"=>0];
        }
        return $return;
    }
    //设置缓存里面的用户手机和用户ID的对应
    public function setMobileUserCache($mobile,$user_id)
    {
        $key = "mobile_".$mobile;
        $this->user_redis->set($key,$user_id);
        $this->user_redis->expire($user_id>0?86400:60);
        return true;
    }
    //设置缓存里面的用户手机和用户ID的对应
    public function getMobileUserCache($mobile)
    {
        $key = "mobile_".$mobile;
        $exists = $this->user_redis->exists($key);
        if($exists)
        {
            $cache = $this->user_redis->get($key);
            return intval($cache);
        }
        else
        {
            return -1;
        }
    }







}
