<?php

namespace App\Http\Controllers;

use App\Libs\AjaxRequest;
use App\Libs\ClientServices;
use App\Services\ActionService;
use App\Services\Data\DataService;
use App\Services\UserService;
use Illuminate\Support\Facades\Redis;
use QL\QueryList;
use Illuminate\Http\Request;

class UploadController extends Controller
{
    public function index(Request $request)
    {
        $action = $request->post("action")??"uploadUserImage";
        if($request->hasFile('file')&&$request->file('file')->isValid()){
            $file=$request->file('file');
        }
        else
        {
            return ["result"=>0,"need_file"=>1,"msg"=>"没有上传文件"];
        }
        $loginConfig = $this->getLoginConfig();
        $userService = new UserService();
        if(in_array($action,$loginConfig))
        {
            $loggedUser = $userService->getUserFromToken();
            if(!isset($loggedUser['userInfo']['user_id']))
            {
                return ["result"=>0,"need_login"=>1,"msg"=>"执行的操作需要登陆,尚未登陆或登陆状态过期"];
            }
        }
        switch($action)
        {
            case "updateUserImage"://更新用户头像
                $return = $userService->uploadUserImage($loggedUser,$file);
                break;
        }
        return $return;
    }
    //获取需要登陆的接口列表
    public function  getLoginConfig()
    {
        return  [
            "updateUserImage"
        ];
    }
}
