<?php

namespace App\Services;

use App\Libs\ClientServices;
use App\Models\CollectResultModel;
use App\Models\MissionModel;
use App\Services\MissionService as oMission;

class InformationService
{
    public function insertData()
    {
        $gameItem = [
            'lol', 'kpl', 'dota2', 'csgo'
        ];

        foreach ($gameItem as $val) {
            switch ($val) {
                case "lol":
                    $this->insertLolInformation();
                    break;
                case "kpl":
                    $this->insertKplInformation();
                    break;
                case "dota2":

                    break;
                case "csgo":

                    break;
                default:

                    break;
            }
        }
        return 'finish';
    }

    //英雄联盟资讯采集
    public function insertLolInformation()
    {
        //23=>'综合',24=>'公告',25=>'赛事',27=>'攻略',28=>'社区'
        $targetItem = [
            23, 24, 25, 27, 28
        ];
        $total = 0;
        foreach ($targetItem as $val) {
            $target = $val;
            $missionModel= new MissionModel();
            $lastPage = 49;//采集最新的50页数据
            for ($i = 0; $i <= $lastPage; $i++) {
                $m = $i + 1;
                $url = 'https://apps.game.qq.com/cmc/zmMcnTargetContentList?r0=jsonp&page=' . $m . '&num=16&target=' . $target . '&source=web_pc';
                $params = [
                    'game' => 'lol',
                    'mission_type' => 'information',
                    'source_link' => $url,
                ];
                $result =  $missionModel->getMissionCount($params);//过滤已经采集过的文章
                $result = $result ?? 0;
                if ($result <= 0) {
                    $data = [
                        "asign_to" => 1,
                        "mission_type" => 'information',//资讯
                        "mission_status" => 1,
                        "game" => 'lol',
                        "source" => 'lol_qq',//
                        'title' => '',
                        'source_link'=>$url,
                        "detail" => json_encode(
                            [
                                "url" => $url,
                                "game" => 'lol',//英雄联盟
                                "source" => 'lol_qq',//资讯
                                "target" => $target
                            ]
                        ),
                    ];
                    $insert = (new oMission())->insertMission($data);
                }

            }

        }
        return true;
    }

    public function insertKplInformation()
    {
        //1761=>新闻,1762=>公告,1763=>活动,1764=>赛事,1765=>攻略
        $targetItem = [
            1761, 1762, 1763, 1764, 1765
        ];
        foreach ($targetItem as $val) {
            $type = $val;
            $missionModel= new MissionModel();
            $lastPage = 50;
            for ($i = 0; $i <= $lastPage; $i++) {
                $m = $i + 1;
                if ($val != 1765) {
                    //资讯
                    $url = 'https://apps.game.qq.com/wmp/v3.1/?p0=18&p1=searchNewsKeywordsList&order=sIdxTime&r0=cors&type=iTarget&source=app_news_search&pagesize=12&page=' . $m . '&id=' . $type;
                    $pageData = curl_get($url);
                } else {
                    //攻略
                    $client = new ClientServices();
                    $url = 'https://apps.game.qq.com/wmp/v3.1/?p0=18&p1=searchNewsKeywordsList&page=' . $m . '&pagesize=15&order=sIdxTime';
                    $refeerer = 'https://pvp.qq.com/web201605/searchResult.shtml';

                    $headers = [
                        'Referer' => $refeerer,
                        'Accept' => 'application/json',
                    ];
                    $pageData = $client->curlGet($url, '', $headers);//攻略
                }

                $cdata = $pageData['msg']['result'] ?? [];
                if ($cdata) {
                    foreach ($cdata as $key => $val) {
                        $detail_url = 'https://apps.game.qq.com/wmp/v3.1/public/searchNews.php?source=pvpweb_detail&p0=18&id=' . $val['iNewsId'];//攻略
                        $params = [
                            'game' => 'kpl',
                            'mission_type' => 'information',
                            'source_link' => $detail_url,
                        ];
                        $result =$missionModel->getMissionCount($params);
                        //过滤已经采集过的文章
                        $result = $result ?? 0;
                        if ($result <= 0) {
                            $data = [
                                "asign_to" => 1,
                                "mission_type" => 'information',//资讯
                                "mission_status" => 1,
                                "game" => 'kpl',
                                "source" => 'pvp_qq',//
                                'title' => $val['sTitle'] ?? '',
                                'source_link'=>$detail_url,
                                "detail" => json_encode(
                                    [
                                        "url" => $detail_url,
                                        "game" => 'kpl',//王者荣耀
                                        "source" => 'pvp_qq',//资讯
                                        'type' => $type,//1761=>新闻,1762=>公告,1763=>活动,1764=>赛事,1765=>攻略
                                    ]
                                ),
                            ];
                            $insert = (new oMission())->insertMission($data);
                        }

                    }
                }
            }
        }
        return true;
    }
}
