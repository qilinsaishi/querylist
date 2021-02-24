<?php

namespace App\Services;

use App\Libs\ClientServices;
use App\Models\CollectResultModel;
use App\Models\InformationModel;
use App\Models\MissionModel;
use App\Services\MissionService as oMission;

class RunesService
{
	//lol符文
    public function insertRunesData()
    {
        $this->insertLolRunes();
        return 'finish';
    }

    //英雄联盟资讯采集
    public function insertLolRunes()
    {
        $missionModel=new MissionModel();
        $url='https://lol.qq.com/act/a20170926preseason/data/cn/runes.json';
        $params = [
            'game' => 'lol',
            'mission_type' => 'runes',
            'source_link' => $url,
        ];
        $result =$missionModel->getMissionCount($params);//过滤已经采集过的文章
        $result = $result ?? 0;
        if ($result <= 0) {
            $data = [
                "asign_to"=>1,
                "mission_type"=>'runes',//符文
                "mission_status"=>1,
                "game"=>'lol',
                "source"=>'lol_qq',//
                'source_link'=>$url,
                "detail"=>json_encode(
                    [
                        "url"=>$url,
                        "game"=>'lol',//英雄联盟
                        "source"=>'lol_qq',//符文
                    ]
                ),
            ];
            $insert = (new oMission())->insertMission($data);
            echo "insert:".$insert.' lenth:'.strlen($data['detail']);
        }

        return true;
    }


}
