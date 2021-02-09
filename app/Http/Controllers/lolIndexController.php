<?php

namespace App\Http\Controllers;

use App\Libs\AjaxRequest;
use App\Libs\ClientServices;
use App\Models\CollectResultModel;
use App\Models\Admin\DefaultConfig;
use App\Services\Data\ExtraProcessService;
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
        foreach($data as $name => $params)
        {
            $dataType = $params['dataType']??$name;
            if(isset($functionList[$dataType]))
            {
                $toSave = 1;
                $dataArr = $redisService->processCache($dataType,$data[($params['cacheWith']??"")]??$params);
                if(is_array($dataArr))
                {

                    //$return[$dataType] = $cache;
                    $toSave = 0;
                }
                else
                {
                    $functionInfo = $functionList[$dataType];
                    $class = $functionInfo['class'];
                    $function = $functionInfo['function'];
                    //$params = $data[$dataType];
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
                    $redisService->saveCache($dataType,$data[($params['cacheWith']??"")]??$params,$dataArr);
                }
                if(isset($dataType) && $dataType='informationList') {
                    $dataArr["data"] = (new ExtraProcessService())->process($dataType,$dataArr["data"]);
                }

                $return[$name] = $dataArr;
            }
        }
        return $return;
    }
    public function refresh()
    {
        $redisService = new RedisService();
        $params = json_decode($this->request->get("params",""),true);
        $dataType = $params['dataType'] ?? 'defaultConfig';
        $keyName= $params['key_name'] ?? '';
        $redisService->refreshCache($dataType,[],$keyName);
    }

}
