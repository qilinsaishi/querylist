<?php

namespace App\Services;

use App\Models\CollectResultModel;
use App\Models\MissionModel;

class CollectResultService
{

    //处理采集数据并且同步到数据库
    public function doCollect($mission_id,$id,$data){
        $collectModel = new CollectResultModel();
        try{
            $rt = $collectModel->updateStatus($id,$data);
            if($rt){
                $missionModel = new MissionModel();
                $insert = $missionModel->updateMission($mission_id, ['mission_status' => 2]);
                if($insert){
                    return true;
                }else{
                    return false;
                }

            }else{
                return false;
            }
        }catch (\Exception $e){
            return  $e->getMessage();
        }

    }

}
