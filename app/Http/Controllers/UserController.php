<?php

namespace App\Http\Controllers;

use App\Libs\AjaxRequest;
use App\Libs\ClientServices;
use App\Services\ActionService;
use App\Services\Data\DataService;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use QL\QueryList;
use GuzzleHttp\Client;


class UserController extends Controller
{

    //依据防火墙配置，将一定时间内的接口返回值直接返回
    public function cacheFirewall($data)
    {
        $redis = Redis::connection('default');
        $firewallConfig = $this->getFirewallConfig();
        if(isset($firewallConfig[$data['type']]))
        {
            $key = "return_cache_".md5(sort($data));
            $exists = $redis->exists($key);
            if($exists)
            {
                return json_decode($redis->get($key),true);
            }
            else
            {
                return [];
            }
        }
    }
    //依据防火墙配置，将一定时间内的借口返回值保存到缓存
    public function returnCache($data,$return)
    {
        $redis = Redis::connection('default');
        $firewallConfig = $this->getFirewallConfig();
        if(isset($firewallConfig[$data['type']]))
        {
            $key = "return_cache_".md5(ksort($data));
            $return["cache_time"] = time();
            $redis->set($key,json_encode($return));
            $redis->expire($key,$firewallConfig[$data['type']]);
            return true;
        }
        else
        {
            return true;
        }
    }
    public function index()
    {
        $data=$this->payload;
        $loginConfig = $this->getLoginConfig();
        $userService = new UserService();
        if(in_array($data['type'],$loginConfig))
        {
            $loggedUser = $userService->getUserFromToken();
            if(!isset($loggedUser['userInfo']['user_id']))
            {
                return ["result"=>0,"need_login"=>1,"msg"=>"执行的操作需要登陆,尚未登陆或登陆状态过期"];
            }
        }
        //获取缓存中的返回值
        //$cache = $this->cacheFirewall($data);
        $cache = [];
        //获取到，直接返回
        if(count($cache))
        {
            return $cache;
        }
        switch($data['type'])
        {
            case "checkMobileAvailable"://检查手机号可用（登陆/注册）
                $return = $userService->checkMobileAvailable($data['params']['mobile']??"",$data['params']['action']??"login");
                break;
            case "sendSmsCode"://发短信（登陆/注册）
                $return = $userService->sendSmsCode($data['params']['mobile']??"",$data['params']['action']??"login");
                break;
            case "loginBySms"://短信登陆
                $return = $userService->loginBySms($data['params']['mobile']??"",$data['params']['code']??"123456");
                break;
            case "regBySms"://短信注册
                $return = $userService->regBySms($data['params']['mobile']??"",$data['params']['code']??"123456",$data['params']['reference_code']??0);
                break;
            case "loginByUser":
                break;
            case "userInfo":
                $return = $userService->getUserFromToken();
                break;
            case "setPassword":
                $return = $userService->setPassword($loggedUser,$data['params']['new_password']??"",$data['params']['new_password_repeat']??"");
                break;
            case "resetPassword":
                $return = $userService->resetPasswordByPassword($loggedUser,$data['params']['password']??"",$data['params']['new_password']??"",$data['params']['new_password_repeat']??"");
                break;
            case "resetPasswordByCode":
                $return = $userService->resetPasswordByCode($data['params']['mobile']??"",$data['params']['code']??"",$data['params']['new_password']??"",$data['params']['new_password_repeat']??"");
                break;
            case "rebuild":
                $return = $userService->rebuildUserCache($loggedUser['userInfo']['user_id']);
                break;
            case "actionTest":
                $return = (new ActionService())->test($data['type'],$loggedUser['userInfo']);
                break;
            default:
                $request = new Request();
                if($request->hasFile('file')&&$request->file('file')->isValid()){
                    echo "666";
                }
                $return = $userService->getUserFromToken();
                break;
        }
        //依据配置将借口返回值放到缓存中
        //sss$this->returnCache($data,$return);
        return $return;
    }
    //获取缓存防火墙配置
    public function  getFirewallConfig()
    {
        return  [
            "sendSms"=>60,
            "loginBySms"=>60,
            "regBySms"=>60,
        ];
    }
    //获取需要登陆的接口列表
    public function  getLoginConfig()
    {
        return  [
            "setPassword","userInfo","resetPassword","rebuild","actionTest"
        ];
    }
}
