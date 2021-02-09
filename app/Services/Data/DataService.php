<?php

namespace App\Services\Data;
use App\Services\Data\PrivilegeService;
use App\Services\Data\RedisService;

class DataService
{
    public function getData($data)
    {
        $redisService = new RedisService();
        $privilegeService = new PrivilegeService();
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
                    $functionProcessCount = $functionInfo['functionProcessCount']??"";
                    if(!$functionCount || $functionCount=="")
                    {
                        $count = 0;
                    }
                    else
                    {
                        $count = $class->$functionCount($params);
                    }
                    if($functionProcessCount!="")
                    {
                        $count = $privilegeService->$functionProcessCount($d,$functionList,$params);
                    }
                    if($functionProcess!="")
                    {
                        $d = $privilegeService->$functionProcess($d,$functionList,$params);
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

}
