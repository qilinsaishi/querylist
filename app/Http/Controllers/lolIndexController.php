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

    public function get()
    {
        $redisService = new RedisService();
        $privilegeService = new PrivilegeService();
        $data=$this->payload;
        $return = [];
        $functionList = $privilegeService->getFunction($data);
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
