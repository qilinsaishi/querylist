<?php

namespace App\Services;
use App\Models\MissionModel as MissionModel;

class MissionService
{
    public function getMission($count = 3)
    {
        $asign = config('app.asign');
        $missionModel = new MissionModel();
        //$mission_list = $missionModel->get()->toArray();
        //print_R($mission_list);
        $mission_list = $missionModel->getMissionByMachine($asign,5);
        print_R($mission_list);
    }
}
