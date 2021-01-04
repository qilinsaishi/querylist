<?php

namespace App\Http\Controllers;

use App\Libs\AjaxRequest;
use App\Libs\ClientServices;
use App\Models\CollectResultModel;
use App\Models\Admin\DefaultConfig;
use Illuminate\Http\Request;


use QL\QueryList;
use GuzzleHttp\Client;

use App\Services\Data\PrivilegeService;
use App\Services\Data\RedisService;

class lolIndexController extends Controller
{

    public function index()
    {

    }
    //matchList 比赛 page/page_size
    //teamList 战队 page/page_size/game
    //tournament 赛事 page/page_size
    //player 选手 page/page_size/game

    public function get()
    {
        $redisService = new RedisService();
        $privilegeService = new PrivilegeService();
        $data=$this->payload;
        $return = [];
        $functionList = $privilegeService->getFunction($data);
        print_R(array_keys($functionList));
        foreach ($functionList as $dataType => $functionInfo)
        {
            $toSave = 1;
            $dataArr = $redisService->processCache($dataType,$data[$dataType]);
            if(is_array($dataArr))
            {

                //$return[$dataType] = $cache;
                $toSave = 0;
            }
            else
            {
                $class = $functionInfo['class'];
                $function = $functionInfo['function'];
                $params = $data[$dataType];
                $d = $class->$function($params);
                $functionCount = $functionInfo['functionCount'];
                $functionProcess = $functionInfo['functionProcess']??"";

                if(!$functionCount || $functionCount=="")
                {
                    $count = 0;
                }
                else
                {
                    $count = $class->$functionCount($params);
                }
                if($functionProcess!="")
                {
                    $d = $privilegeService->$functionProcess($d,$functionList);
                }
                $dataArr = ['data'=>$d,'count'=>$count];
            }
            if($toSave==1)
            {
                $redisService->saveCache($dataType,$data[$dataType],$dataArr);
            }
            $return[$dataType] = $dataArr;
        }
        return $return;
    }

}
