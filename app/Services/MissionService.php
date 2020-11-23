<?php

namespace App\Services;
use App\Models\MissionModel as MissionModel;

class MissionService
{
    public function getMission($count = 3)
    {
        $asign = config('app.asign');//服务器
        $missionModel = new MissionModel();

        $mission_list = $missionModel->getMissionByMachine($asign,5);

        dd($mission_list);
    }
}
