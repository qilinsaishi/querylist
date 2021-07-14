<?php

namespace App\Services;

use App\Helpers\Jwt;
use App\Libs\AjaxRequest;
use App\Libs\ClientServices;
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
                    $this->$funcName($params);
                }
            }
            /*
            print_R($action);
            $params = explode("|",$action['params']);
            print_R($params);
            die();
            */

        }
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
    public function regReferenceCredit($params)
    {
        $reference_user_id = intval($params['0']);
        if($reference_user_id<=0)
        {
            $return = ['result'=>0];git
        }
    }
}
