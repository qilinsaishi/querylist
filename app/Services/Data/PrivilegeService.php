<?php

namespace App\Services\Data;

class PrivilegeService
{
    //获取各个数据类型对应的类库优先级列表以及获取方法
    public function getPriviliege()
    {
        $privilegeList = [
            "matchList"=>[
                'list'=>[
                    'App\Models\Match\cpseo\matchListModel',
                    'App\Models\Match\chaofan\matchListModel'
                ],
                'function'=>"getMatchList",//获取数据方法
                'functionCount'=>"getMatchCount",//获取列表方法

            ],
            "tournament"=>[
                'list'=> [
                    'App\Models\Match\cpseo\tournamentModel',
                    'App\Models\Match\chaofan\tournamentModel'
                ],
                'function'=>"getTournamentList",
                'functionCount'=>"getTournamentCount",
            ],
            "teamList"=>[
                'list'=>[
                    'App\Models\Match\cpseo\teamModel',
                    'App\Models\Match\chaofan\teamModel',
                ],
                'function'=>"getTeamList",
                'functionCount'=>"getTeamCount",

            ]
        ];
        return $privilegeList;
    }

    public function getFunction($data)
    {
        //获取各个数据类型对应的类库优先级列表以及获取方法
        $priviliegeList = $this->getPriviliege();
        $classList = [];
        $functionList = [];
        foreach($data as $dataType => $params)
        {
            //echo "found type:".$dataType."\n";
            $found = 0;
            if(isset($priviliegeList[$dataType]))
            {
                foreach($priviliegeList[$dataType]['list'] as $modelName)
                {
                    $classList = $this->getClass($classList,$modelName);
                    if(!isset($functionList[$dataType]))
                    {
                        if(isset($classList[$modelName]))
                        {
                            if(method_exists($classList[$modelName],$priviliegeList[$dataType]['function']))
                            {
                                //echo "class:".$modelName.",function:".$priviliegeList[$dataType]['function']." found\n";
                                $functionList[$dataType] = ["className"=>$modelName,"class"=>$classList[$modelName],"function"=>$priviliegeList[$dataType]['function']];
                                if(method_exists($classList[$modelName],$priviliegeList[$dataType]['functionCount']))
                                {
                                    $functionList[$dataType]['functionCount'] =  $priviliegeList[$dataType]['functionCount'];
                                }
                                else
                                {
                                    $functionList[$dataType]['functionCount'] = "";
                                }
                                $found = 1;
                            }
                            else
                            {
                                //echo "class:".$modelName.",function:".$priviliegeList[$dataType]['function']." not found\n";
                            }
                        }
                        else
                        {
                            //echo "class:".$modelName.",not found\n";
                        }
                    }
                }
            }
            if($found==0)
            {
                //echo "dataType:".$dataType.",function not found\n";
            }
        }
        return $functionList;
    }
    public function getClass($classList, $modelClassName)
    {
        //判断类库存在
        $exist = class_exists($modelClassName);
        if (!$exist) {

        } else {
            //之前没有初始化过
            if (!isset($classList[$modelClassName])) {
                //初始化，存在列表中
                $modelClass = new $modelClassName;
                $classList[$modelClassName] = $modelClass;
            } else {
                ////直接调用
                //$modelClass = $classList[$modelClassName];
            }
        }
        return $classList;
    }
}
