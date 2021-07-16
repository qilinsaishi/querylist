<?php

namespace App\Services;

use App\Helpers\Jwt;
use App\Libs\AjaxRequest;
use App\Libs\ClientServices;
use App\Models\User\ActionLogModel;
use App\Models\User\LoginLogModel;
use App\Services\UserService;
use App\Models\User\CoinLogModel;
use App\Models\User\CreditLogModel;
use App\Models\User\ActionModel;

use Illuminate\Support\Facades\Redis;
use QL\QueryList;

class ActionService
{
    public function __construct()
    {
        $this->user_redis = Redis::connection('user_redis');
        $this->actionModel = new ActionModel();
        $this->actionLogModel = new ActionLogModel();
    }

    public function test($api_action,$userInfo)
    {
        $actionList = $this->generateActionList($api_action);
        foreach($actionList as $action)
        {
            if(method_exists($this,$action['action']))
            {
                $params = explode("|",$action['params']);
                foreach($params as $k => $v)
                {
                    $params[$k] = $userInfo[$v]??$v;
                    $funcName = $action['action'];
                }
                //针对自己
                if($action['user']=="self")
                {
                    $user_id = $userInfo['user_id'];
                }
                //针对上级
                if($action['user']=="reference")
                {
                    $user_id = $userInfo['reference_user_id'];
                }
                echo "process action:".$action['action_id'].",user:".$user_id;
                echo "\n";
                $checkFunction = $this->$funcName($user_id,$params,$action);
                if($checkFunction['result'])
                {
                    if(isset($checkFunction['already']))
                    {
                        return true;
                    }
                    else
                    {
                        $this->actionLog($action,$user_id,$checkFunction['dateRange']);

                    }
                }
                else
                {
                    return false;
                }
            }
        }
    }
    public function actionLog($action,$user_id,$dateRange)
    {
        $actionLog = ['user_id'=>$user_id,"action"=>$action['action'],"action_id"=>$action['action_id'],"start_date"=>$dateRange['start_date'],"end_date"=>$dateRange['end_date']];
        $this->actionLogModel->insertActionLog($actionLog);
        $this->rebuildActionFinishCountByAction($user_id,$action['action_id'],$dateRange['start_date'],$dateRange['end_date']);
        return true;
    }
    public function generateActionList($api_action)
    {
        $actionList = $this->actionModel->getActionList();
        $return = [];
        foreach($actionList as $action)
        {
            if($api_action == $action['bind_with'])
            {
                $return[] = $action;
            }
        }
        return $return;
    }
    public function regReferenceCredit($reference_user_id,$params,$action)
    {
        $user_count = $params['0'];
        $date_count = $params['1'];
        $coin = $params['2'];
        if($reference_user_id<=0)
        {
            $return = ['result'=>0];
        }
        else
        {
            $dateRange = processDateRange(date("Y-m-d"),$action['frequency']);
            $checkFinished = $this->checkActionFinishCount($reference_user_id,$action['action_id'],$dateRange['start_date'],$dateRange['end_date']);
            if($checkFinished>=$action['count'])
            {
                $return = ['result'=>1,"dateRange"=>$dateRange,"already"=>1];
            }
            else
            {
                $count = (new LoginLogModel())->getUserLoginDateCountByReference($reference_user_id,$dateRange['start_date'],$dateRange['end_date']);
                $finishedUser = 0;
                $success = 0;
                foreach($count as $user)
                {
                    if($user['date']>=$date_count)
                    {
                        $finishedUser++;
                        if($finishedUser>=$user_count)
                        {
                            $success = 1;
                            break;
                        }
                    }
                }
                if($success)
                {

                    (new UserService())->addCredit($reference_user_id,$coin,1,$action['action'],$dateRange['start_date']."-".$dateRange['end_date']."推荐用户登陆数量达标，发放积分".$coin);
                    die();
                    $return = ['result'=>1,"dateRange"=>$dateRange];
                }
                else
                {
                    $return = ['result'=>0];
                }
            }

        }
        return $return;
    }
    //检查用户对任务的完成数量
    public function checkActionFinishCount($user_id,$action_id,$start_date,$end_date)
    {
        $key = "action_log_count_by_action_".$user_id."_".$action_id."_".$start_date."_".$end_date;
        //检查缓存是否存在
        $exists = $this->user_redis->exists($key);
        if($exists)
        {
            $return = $this->user_redis->get($key);
            echo "cached";
        }
        else
        {
            $return = $this->rebuildActionFinishCountByAction($user_id,$action_id,$start_date,$end_date);
        }
        return $return;
    }
    //刷新任务完成数量的缓存
    public function rebuildActionFinishCountByAction($user_id,$action_id,$start_date,$end_date)
    {
        $key = "action_log_count_by_action_".$user_id."_".$action_id."_".$start_date."_".$end_date;
        $count = $this->actionLogModel->getActionLogCountByAction($user_id,$action_id,$start_date,$end_date);
        $this->user_redis->set($key,$count);
        $this->user_redis->expire($key,7*86400);
        return $count;
    }
}
