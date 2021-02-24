<?php

namespace App\Services;

use App\Libs\ClientServices;
use App\Models\CollectResultModel;
use App\Models\InformationModel;
use App\Models\MissionModel;
use App\Services\MissionService as oMission;

class InscriptionService
{
    public function insertInscriptionData()
    {
        $this->insertLolInscription();
        return 'finish';
    }

    //英雄联盟资讯采集
    public function insertLolInscription()
    {
        $missionModel=new MissionModel();
        $url = 'https://pvp.qq.com/web201605/js/ming.json';
        $params = [
            'game' => 'kpl',
            'mission_type' => 'inscription',
            'source_link' => $url,
        ];
        $result =$missionModel->getMissionCount($params);//过滤已经采集过的文章
        $result = $result ?? 0;
        if ($result <= 0) {
            $data = [
                "asign_to" => 1,
                "mission_type" => 'inscription',//铭文
                "mission_status" => 1,
                "game" => 'kpl',
                "source" => 'pvp_qq',//铭文
                'source_link'=>$url,
                "detail" => json_encode(
                    [
                        "url" => $url,
                        "game" => 'kpl',//王者荣耀
                        "source" => 'pvp_qq',//王者荣耀官网

                    ]
                ),
            ];
            $insert = (new oMission())->insertMission($data);
            echo "insert:" . $insert . ' lenth:' . strlen($data['detail']);
        }

        return true;
    }


}
