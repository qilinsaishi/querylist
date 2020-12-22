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
                    ['model'=>'App\Models\Match\#source#\matchListModel','source'=>'cpseo'],
                    ['model'=>'App\Models\Match\#source#\matchListModel','source'=>'chaofan'],
                ],
                'withSource'=>1,
                'function'=>"getMatchList",//获取数据方法
                'functionCount'=>"getMatchCount",//获取列表方法
                'functionProcess'=>"processMatchList",//格式化的处理方法
            ],
            "tournament"=>[
                'list'=> [
                    ['model'=>'App\Models\Match\#source#\tournamentModel','source'=>"chaofan"],
                    ['model'=>'App\Models\Match\#source#\tournamentModel','source'=>"cpseo"],
                ],
                'withSource'=>1,
                'function'=>"getTournamentList",
                'functionCount'=>"getTournamentCount",
            ],
            "teamList"=>[
                'list'=>[
                    ['model'=>'App\Models\Match\#source#\teamModel','source'=>'cpseo'],
                    ['model'=>'App\Models\Match\#source#\teamModel','source'=>'chaofan'],
                ],
                'withSource'=>1,
                'function'=>"getTeamList",
                'functionCount'=>"getTeamCount",
                'functionSingle'=>"getTeamById",
            ]
        ];
        return $privilegeList;
    }

    public function getFunction($data,$currentSource = "")
    {
        $currentSource = "";
        //获取各个数据类型对应的类库优先级列表以及获取方法
        $priviliegeList = $this->getPriviliege();
        $classList = [];
        $functionList = [];
        foreach($data as $dataType => $params)
        {
            //echo "found type:".$dataType."\n";
           // echo "currentSource:".$currentSource."\n";
            $found = 0;
            if(isset($priviliegeList[$dataType]))
            {
                if($currentSource=="" && $priviliegeList[$dataType]['withSource']==1)
                {
                    foreach($priviliegeList[$dataType]['list'] as $detail)
                    {
                        $modelName = $detail['model'];
                        $currentSource = $currentSource==""?$detail['source']:$currentSource;
                        $modelName = str_replace("#source#",$detail['source'],$modelName);
                        $classList = $this->getClass($classList,$modelName);
                        if(!isset($functionList[$dataType]))
                        {
                            if(isset($classList[$modelName]))
                            {
                                if(method_exists($classList[$modelName],$priviliegeList[$dataType]['function']))
                                {
                                    //echo "class:".$modelName.",function:".$priviliegeList[$dataType]['function']." found\n";
                                    $functionList[$dataType] = ["className"=>$modelName,"class"=>$classList[$modelName],"function"=>$priviliegeList[$dataType]['function']];
                                    if(isset($priviliegeList[$dataType]['functionCount']) && method_exists($classList[$modelName],$priviliegeList[$dataType]['functionCount']))
                                    {
                                        $functionList[$dataType]['functionCount'] =  $priviliegeList[$dataType]['functionCount'];
                                    }
                                    else
                                    {
                                        $functionList[$dataType]['functionCount'] = "";
                                    }
                                    if(isset($priviliegeList[$dataType]['functionSingle']) && method_exists($classList[$modelName],$priviliegeList[$dataType]['functionSingle']))
                                    {
                                        $functionList[$dataType]['functionSingle'] =  $priviliegeList[$dataType]['functionSingle'];
                                    }
                                    else
                                    {
                                        $functionList[$dataType]['functionSingle'] = "";

                                    }
                                    if(isset($priviliegeList[$dataType]['functionProcess']))
                                    {
                                        $functionList[$dataType]['functionProcess'] =  $priviliegeList[$dataType]['functionProcess'];
                                    }
                                    else
                                    {
                                        $functionList[$dataType]['functionProcess'] = "";

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
                            $functionList[$dataType]['source']=$currentSource;//$priviliegeList[$dataType]['source'];
                        }
                    }
                }
                elseif($currentSource!="" && $priviliegeList[$dataType]['withSource']==1)
                {
                    $functionList[$dataType]['source'] = $currentSource;//$priviliegeList[$dataType]['source'];
                    $list = array_combine(array_column($priviliegeList[$dataType]['list'],"source"),array_column($priviliegeList[$dataType]['list'],"model"));
                    if(isset($list[$currentSource]))
                    {
                        $modelName = $list[$currentSource];
                        $modelName = str_replace("#source#",$currentSource,$modelName);
                        $classList = $this->getClass($classList,$modelName);
                        if(method_exists($classList[$modelName] ??[],$priviliegeList[$dataType]['function']))
                        {
                            $functionList[$dataType] = ["className"=>$modelName,"class"=>$classList[$modelName],"function"=>$priviliegeList[$dataType]['function']];
                            $found = 1;
                            if(isset($priviliegeList[$dataType]['functionCount']) && method_exists($classList[$modelName],$priviliegeList[$dataType]['functionCount']))
                            {
                                $functionList[$dataType]['functionCount'] =  $priviliegeList[$dataType]['functionCount'];

                            }
                            else
                            {
                                $functionList[$dataType]['functionCount'] = "";
                            }
                            if(isset($priviliegeList[$dataType]['functionSingle']) && method_exists($classList[$modelName],$priviliegeList[$dataType]['functionSingle']))
                            {
                                $functionList[$dataType]['functionSingle'] =  $priviliegeList[$dataType]['functionSingle'];
                            }
                            else
                            {
                                $functionList[$dataType]['functionSingle'] = "";

                            }
                        }
                    }
                    if($found == 0)
                    {
                        foreach($priviliegeList[$dataType]['list'] as $detail)
                        {
                            $modelName = $detail['model'];
                            $modelName = str_replace("#source#",$detail['source'],$modelName);
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
                                        if(isset($priviliegeList[$dataType]['functionSingle']) && method_exists($classList[$modelName],$priviliegeList[$dataType]['functionSingle']))
                                        {
                                            $functionList[$dataType]['functionSingle'] =  $priviliegeList[$dataType]['functionSingle'];
                                        }
                                        else
                                        {
                                            $functionList[$dataType]['functionSingle'] = "";

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
                    $functionList[$dataType]['source']=$currentSource;//$priviliegeList[$dataType]['source'];
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
    public function processMatchList($data,$functionList)
    {
        //die();
        if(isset($functionList['teamList']) && isset($functionList['teamList']['functionSingle']))
        {

        }
        else
        {
            $f = $this->getFunction(['teamList'=>[]],$functionList['matchList']['source']);
            if(isset($f['teamList']['class']))
            {
                $functionList["teamList"] = $f['teamList'];
            }
        }
        if(!isset($functionList["teamList"]["class"]))
        {
            return $data;
        }
        $modelClass = $functionList["teamList"]["class"];
        $functionSingle = $functionList["teamList"]['functionSingle'];
        $teamList = [];
        foreach($data as $key => $matchInfo)
        {
            if(!isset($teamList[$matchInfo['home_id']]))
            {
                $teamInfo = $modelClass->$functionSingle($matchInfo['home_id']);
                if(isset($teamInfo['team_id']))
                {
                    $teamList[$matchInfo['home_id']] = $teamInfo;
                }
            }
            if(!isset($teamList[$matchInfo['away_id']]))
            {
                $teamInfo = $modelClass->$functionSingle($matchInfo['away_id']);
                if(isset($teamInfo['away_id']))
                {
                    $teamList[$matchInfo['away_id']] = $teamInfo;
                }
            }
            $data[$key]['home_team_info'] = $teamList[$matchInfo['home_id']]??[];
            $data[$key]['away_team_info'] = $teamList[$matchInfo['away_id']]??[];
        }
        return $data;
    }
}
