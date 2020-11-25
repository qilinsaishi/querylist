<?php

namespace App\Services;
use App\Models\CollectResultModel;
use App\Models\MissionModel as MissionModel;
use function PHPUnit\Framework\fileExists;

class MissionService
{
    public function processMission($game,$source)
    {
        $mission_list = $this->getMission($game,$source,20);
        $collectModel = new CollectResultModel();
        $missionModel = new MissionModel();
        $classList = [];
            foreach ($mission_list as $key=>$mission) {
                $mission['detail'] = json_decode($mission['detail'],true);
                if (isset($mission['source']))
                {
                    $className = 'App\Collect\\' . $mission['mission_type'] . '\\' . $mission['game'] . '\\' . $mission['source'];
                    $exist = class_exists($className);
                    if (!$exist)
                    {
                        echo $className . " not found\n";
                    }
                    else
                    {
                        if (!isset($classList[$className]))
                        {
                            $class = new $className;
                            $classList[$className] = $class;
                        }
                        else
                        {
                            $class = $classList[$className];
                        }
                        $result=$class->collect($mission);
                        if($result){
                            try{
                                $rt = $collectModel->insertCollect($result);
                                if($rt){
                                    $missionModel->updateMission($mission['mission_id'], ['mission_status' =>2]);
                                }else{
                                    $return=false;
                                }

                            }catch (\Exception $e){
                                return  $e->getMessage();
                            }



                        }else{
                            return false;
                        }
                    }
                }
                sleep(rand(10,20));
                echo 222222;

            }

    }
    public function getMission($game,$source,$count = 3)
    {
        $asign = config('app.asign');
        $missionModel = new MissionModel();
        $mission_list = $missionModel->getMissionByMachine($asign,$count,$game,$source);
         return ($mission_list) ;
    }
    public function insertMission($data)
    {
        return (new MissionModel())->insertMission($data);
    }
}
