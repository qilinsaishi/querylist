<?php

namespace App\Collect\team\kpl;

use App\Models\CollectResultModel;
use App\Models\MissionModel;
use App\Services\CollectResultService;


class baidu_baike
{
    public function collect($arr)
    {
        $model = new CollectResultService();
        $collectModel = new CollectResultModel();
        $id = $arr['detail']['id'] ?? '';
        $url = $arr['detail']['url'] ?? '';
        $res = $model->getCollectData($url);
        $cdata = [];
        if (!empty($res)) {
            $cdata = [
                'mission_id' => $arr['mission_id'],
                'content' => json_encode($res),
                'game' => $arr['game'],
                'mission_type'=>$arr['mission_type'],
                'source'=>$arr['source'],
                'status' => 1
            ];
            $rt = $collectModel->where('id', $id)->update($cdata);
            if ($rt) {
                $missionModel = new MissionModel();
                $insert = $missionModel->updateMission($arr['mission_id'], ['mission_status' => 2]);
                echo "insert:" . $insert;

            }
        }

    }
}
