<?php

namespace App\Services;

use App\Models\CollectResultModel;
use App\Models\MissionModel;

class CollectResultService
{

    //处理采集数据并且同步到数据库
    public function doCollect($data){
        $collectModel = new CollectResultModel();
        try{
            $rt = $collectModel->insertCollect($data);
            if($rt){
              $return=true;
            }else{
                $return=false;
            }
            return  $return;
        }catch (\Exception $e){
            return  $e->getMessage();
        }

    }

}
