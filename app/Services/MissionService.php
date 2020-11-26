<?php

namespace App\Services;
use App\Models\CollectResultModel as CollectModel;
use App\Models\MissionModel as MissionModel;

class MissionService
{
    //爬取数据
    public function collect($game="",$source="")
    {
        //获取爬取任务列表
        $mission_list = $this->getMission($game,$source,20);
        $collectModel = new CollectModel();
        $missionModel = new MissionModel();
        //初始化空的类库列表
        $classList = [];
        //循环任务列表
        foreach ($mission_list as $key=>$mission)
        {
            //数据解包
            $mission['detail'] = json_decode($mission['detail'],true);
            //如果必要元素存在
            if (isset($mission['source']))
                {
                    //生成类库路径
                    $className = 'App\Collect\\' . $mission['mission_type'] . '\\' . $mission['game'] . '\\' . $mission['source'];
                    //判断类库存在
                    $exist = class_exists($className);
                    //如果不存在
                    if (!$exist)
                    {
                        echo $className . " not found\n";
                    }
                    else
                    {
                        //之前没有初始化过
                        if (!isset($classList[$className]))
                        {
                            //初始化，存在列表中国呢
                            $class = new $className;
                            $classList[$className] = $class;
                        }
                        else
                        {
                            //直接调用
                            $class = $classList[$className];
                        }
                        //执行爬取操作
                        $result=$class->collect($mission);
                        //如果爬取成功
                        if($result)
                        {
                            try{
                                //保存结果
                                $rt = $collectModel->insertCollectResult($result);
                                //如果保存成功
                                if($rt){
                                    //更新任务状态，以后改成接口模式
                                    $missionModel->updateMission($mission['mission_id'], ['mission_status' =>2]);
                                }else{
                                    $return=false;
                                }
                            }catch (\Exception $e){
                                return  $e->getMessage();
                            }
                        }
                        else
                        {
                            return false;
                        }
                    }
                }
                //随机等待
                $sleep = rand(10,20);
                sleep($sleep);
                echo $sleep."\n";
            }
    }
    //爬取数据
    public function process($game="kpl",$source="")
    {
        //获取爬取任务列表
        $result_list = $this->getResult($game,$source,100);
        $collectModel = new CollectModel();
        $missionModel = new MissionModel();
        //初始化空的类库列表
        $classList = [];
        //循环任务列表
        foreach ($result_list as $key=>$result)
        {
            //数据解包
            $result['content'] = json_decode($result['content'],true);
            //如果结果数组非空
            if (count($result['content'])>0)
            {
                //生成类库路径
                $className = 'App\Collect\\' . $result['mission_type'] . '\\' . $result['game'] . '\\' . $result['source'];
                //判断类库存在
                $exist = class_exists($className);
                //如果不存在
                if (!$exist)
                {
                    echo $className . " not found\n";
                }
                else
                {
                    //之前没有初始化过
                    if (!isset($classList[$className]))
                    {
                        //初始化，存在列表中国呢
                        $class = new $className;
                        $classList[$className] = $class;
                    }
                    else
                    {
                        //直接调用
                        $class = $classList[$className];
                    }
                    //执行爬取操作
                    $processResult=$class->process($result);
                    echo "id:".$result['id']."\n";
                    print_R($processResult);
                    continue;
                    //如果爬取成功
                    if($result)
                    {
                        try{
                            //保存结果
                            $rt = $collectModel->insertCollectResult($result);
                            //如果保存成功
                            if($rt){
                                //更新任务状态，以后改成接口模式
                                $missionModel->updateMission($result['mission_id'], ['mission_status' =>2]);
                            }else{
                                $return=false;
                            }
                        }catch (\Exception $e){
                            return  $e->getMessage();
                        }
                    }
                    else
                    {
                        return false;
                    }
                }
            }
            //随机等待
            $sleep = rand(10,20);
            sleep($sleep);
            echo $sleep."\n";
        }
    }
    public function getMission($game,$source,$count = 3)
    {
        $asign = config('app.asign');
        $missionModel = new MissionModel();
        $mission_list = $missionModel->getMissionByMachine($asign,$count,$game,$source);
         return ($mission_list) ;
    }
    public function getResult($game,$source,$count = 3)
    {
        $collectModel = new CollectModel();
        $mission_list = $collectModel->getResult($count,$game,$source);
        return ($mission_list) ;
    }
    public function insertMission($data)
    {
        return (new MissionModel())->insertMission($data);
    }
}
