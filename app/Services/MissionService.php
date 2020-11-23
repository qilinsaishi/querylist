<?php

namespace App\Services;
use App\Models\MissionModel as MissionModel;

class MissionService
{
    public function processMission()
    {
        $mission_list = $this->getMission(10);
        foreach($mission_list as $mission)
        {

            $detail = json_decode($mission['detail'],true);
            if(!empty($detail)){
                if(isset($detail['source']))
                {
                    $root = 'App\Collect\\' . $mission['mission_type'] . '\\'.$detail['source'];
                    //$className = "o".$mission->detail->source;
                    $class = new $root;
                    $class->collect($mission);

                }
            }

        }
    }
    public function getMission($count = 3)
    {
        $asign = config('app.asign');
        $missionModel = new MissionModel();
        $mission_list = $missionModel->getMissionByMachine($asign,5);
         return ($mission_list) ;
    }
    public function insertMission($data)
    {
        return (new MissionModel())->insertMission($data);
    }
}
