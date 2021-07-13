<?php

namespace App\Services;

use App\Helpers\Jwt;
use App\Libs\AjaxRequest;
use App\Libs\ClientServices;
use App\Models\User\PasswordLogModel;
use App\Models\User\UserModel;
use App\Models\User\LoginLogModel;

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
                $return = ['result'=>($action=="login"?1:0),'msg'=>"手机号码已经注册","user_id"=>$mobileUserCache];
            }
            elseif($mobileUserCache==0)
            {
                $return = ['result'=>($action=="login"?0:1),'msg'=>"手机号码尚未注册"];
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
                $sendSms = (new AliyunService())->sms($mobile,"common",$params = ["code"=>$code2Send]);
                if($sendSms)
                {
                    //标记缓存
                    $this->user_redis->set($code['keyIfExist'],json_encode(['code'=>$code2Send,'send_time'=>time()]));
                    $this->user_redis->expire($code['keyIfExist'], $cacheTime);
                    $return = ['result'=>1,'msg'=>"短信已发送,".intval($cacheTime/60)."分钟内有效，请注意查收"];
                }
                else
                {
                    $return = ['result'=>0,'msg'=>"短信发送失败，请稍后再试，请注意查收"];
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
        $action = "login";
        //检查手机号是否可用
        $available = $this->checkMobileAvailable($mobile,$action);
        if($available['result'])
        {
            //获取缓存中的验证码记录
            $currentCode = $this->getSmsCode($mobile,$action);
            if($currentCode['result'])
            {
                //验证码正确
                if($currentCode['code']==trim($code))
                {
                    //以用户ID方式登陆
                    $return = $this->logById($available['user_id']);
                    //清除缓存里面的发送记录
                    $this->deleteSmsRedisKey($mobile,$action);
                }
                else
                {
                    $return = ['result'=>0,"msg"=>"验证码有误,请重新尝试"];
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
    //密码登陆
    public function loginByUser($mobile,$password)
    {

    }
    //短信注册
    public function regBySms($mobile,$code,$referenceCode="")
    {
        $action = "reg";
        //检查手机号是否可用
        $available = $this->checkMobileAvailable($mobile,$action);
        //可用
        if($available['result'])
        {
            //获取缓存中的验证码记录
            $currentCode = $this->getSmsCode($mobile,$action);
            if($currentCode['result'])
            {
                //验证码正确
                if($currentCode['code']==trim($code))
                {
                    //如果有包含推荐码
                    if($referenceCode != "")
                    {
                        $referenceUser = $this->userModel->getUserByReference($referenceCode);
                    }
                    //尝试注册用户
                    $reg = $this->reg(["mobile"=>$mobile,"reference_user_id"=>$referenceUser['user_id']??0,"reg_type"=>1,"reference_code"=>md5(trim($mobile)),"nick_name"=>$this->generateNickName("sms")]);
                    if($reg['result']>0)
                    {
                        if(isset($referenceUser['user_id']) && $referenceUser['user_id']>0)
                        {
                            $this->userModel->updateUser($referenceUser['user_id'],["reference_count"=>$this->userModel->getUserCountByReference($referenceUser['user_id'])]);
                        }
                        //清除缓存里面的发送记录
                        $this->deleteSmsRedisKey($mobile,$action);
                        //设置手机号码和用户ID的缓存
                        $this->setMobileUserCache($mobile,$reg['user_id']);
                        //登陆
                        $login = getFieldsFromArray($this->logById($reg['user_id']),"authToken,userInfo");
                        $return = ['result'=>1,"msg"=>"注册成功"];
                        $return = array_merge($return,$login);
                    }
                    else
                    {
                        $return = ['result'=>0,"msg"=>"注册失败"];
                    }
                }
                else
                {
                    $return = ['result'=>0,"msg"=>"验证码有误,请重新尝试"];
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
    //以用户ID登陆
    public function logById($user_id)
    {
        //获取用户数据
        $userInfo = $this->userModel->getUserById($user_id);
        if(isset($userInfo['user_id']))
        {
            $currentTime = time();
            //生成用户信息有效时间
            $userInfo['expire_time'] = $currentTime + 7*86400;
            //摘取一部分返回用
            $userInfo4Login = getFieldsFromArray($userInfo,"user_id,nick_name,mobile,credit");
            //生成token
            $token = Jwt::getToken($userInfo);
            //写登录记录
            $login_ip = $_SERVER["REMOTE_ADDR"];
            $loginLog = ['user_id'=>$userInfo['user_id'],"reference_user_id"=>$userInfo['reference_user_id'],"login_type"=>1,"login_ip"=>ip2long($login_ip)];
            $log = (new LoginLogModel())->insertLoginLog($loginLog);
            $return = ['result'=>1,"authToken"=>$token,"msg"=>"登陆成功","userInfo"=>$userInfo4Login];
        }
        else
        {
            $return = ['result'=>0,"msg"=>"登陆失败"];
        }
        return $return;
    }
    //设置缓存里面的用户手机和用户ID的对应
    public function setMobileUserCache($mobile,$user_id)
    {
        $key = "mobile_".$mobile;
        $this->user_redis->set($key,$user_id);
        $this->user_redis->expire($key,$user_id>0?86400:60);
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
    //生成随机用户名
    public function generateNickName($type="sms")
    {
        if($type=="sms")
        {
            return "短信注册网友".date("ymdhis").sprintf("%03d",rand(1,999));
        }
        else
        {
            return "用户名注册网友".date("ymdhis").sprintf("%03d",rand(1,999));
        }
    }
    public function authList()
    {
        return ["updatePassword"];
    }
    public function test()
    {
        return Jwt::getUserId(\Request::header('auth-token'));
    }
    public function getUserFromToken()
    {
        return Jwt::getUserId(\Request::header('auth-token'));
    }
    //更新用户密码
    public function setPassword($userInfo,$new_password,$new_password_repeat)
    {
        $userInfo = $this->userModel->getUserById($userInfo['user_id']);
        //只有密码为空的用户才可以设置密码
        if($userInfo['password']=="")
        {
            //检查密码有效性
            $checkPassword = $this->checkNewPassword("",$new_password,$new_password_repeat);
            if($checkPassword['result']==1)
            {
                //更新用户
                $updateUser = $this->userModel->updateUser($userInfo['user_id'],['password'=>$checkPassword['password']]);
                if($updateUser)
                {
                    //写密码更新记录
                    $login_ip = $_SERVER["REMOTE_ADDR"];
                    $passwordLog = ["user_id"=>$userInfo['user_id'],"update_ip"=>ip2long($login_ip)];
                    $log = (new PasswordLogModel())->insertPasswordLog($passwordLog);
                    $return = ['result'=>1,"msg"=>"密码重置成功","need_login"=>1];
                    //清空token?
                }
                else
                {
                    $return = ['result'=>0,"msg"=>"密码设置失败"];
                }
            }
            else
            {
                $return = $checkPassword;
            }
        }
        else
        {
            $return = ['result'=>0,"msg"=>"当前用户状态无法设置密码"];
        }
        return $return;
    }
    //检查两次输入的新密码的有效性
    public function checkNewPassword($password,$new_password,$new_password_repeat)
    {
        $s1 = strlen($new_password);
        $s2 = strlen($new_password_repeat);
        //密码长度6-10位
        if($s1<6 || $s1>10 || $s2<6 || $s2>10)
        {
            $return = ['result'=>0,"msg"=>"密码长度有误"];
        }
        else
        {
            //正则校验只包含数字英文
            $p1 = preg_match("/^[a-zA-Z0-9]+$/u",$new_password);
            $p2 = preg_match("/^[a-zA-Z0-9]+$/u",$new_password_repeat);
            if($p1*$p2==0)
            {
                $return = ['result'=>0,"msg"=>"密码中只能包含数字和英文字母"];
            }
            else
            {
                //校验两次密码是否一致
                if($new_password != $new_password_repeat)
                {
                    $return = ['result'=>0,"msg"=>"两次输入的密码不一致"];
                }
                else
                {
                    if(md5(md5($new_password)) == $password)
                    {
                        $return = ['result'=>0,"msg"=>"新老密码不能一致"];
                    }
                    else
                    {
                        $return = ['result'=>1,"password"=>md5(md5($new_password))];
                    }
                }
            }

        }
        return $return;
    }
    public function expireToken($token)
    {
        return true;
    }
}
