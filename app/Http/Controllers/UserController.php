<?php

namespace App\Http\Controllers;

use App\Libs\AjaxRequest;
use App\Libs\ClientServices;
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
        //获取缓存中的返回值
        $cache = $this->cacheFirewall($data);
        //获取到，直接返回
        if(count($cache))
        {
            return $cache;
        }
        $userService = new UserService();
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
                $return = $userService->regBySms($data['params']['mobile']??"",$data['params']['code']??"123456");
                break;
            case "loginByUser":
                break;
        }
        //依据配置将借口返回值放到缓存中
        $this->returnCache($data,$return);
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
}
