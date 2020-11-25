<?php

namespace App\Services;
use App\Models\MissionModel as MissionModel;
use function PHPUnit\Framework\fileExists;

class MissionService
{
    public function processMission($game,$source)
    {
        $mission_list = $this->getMission($game,$source,10);
        $classList = [];
        foreach ($mission_list as $mission) {
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
                $class->collect($mission);
                }
            }
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
