<?php

namespace App\Services;

use App\Libs\ClientServices;
use App\Models\CollectResultModel;
use App\Models\InformationModel;
use App\Models\MissionModel;
use App\Services\MissionService as oMission;
use QL\QueryList;

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

                   // $this->insertLolInformation();
                    break;
                case "kpl":
                   // $this->insertKplInformation();
                    break;
                case "dota2":
                    $typeList=['news','gamenews','competition','news_update'];
                    $raidersList=['raiders','newer','step','skill'];
                    foreach ($typeList as $val){
                        $this->insertDota2Information($val);
                    }
                    foreach ($raidersList as $val){
                        $this->insertDota2Raiders($val);
                    }

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
            $missionModel = new MissionModel();
            $lastPage = 9;//采集最新的50页数据
            for ($i = 0; $i <= $lastPage; $i++) {
                $t1 = microtime(true);
                $m = $i + 1;
                $url = 'https://apps.game.qq.com/cmc/zmMcnTargetContentList?r0=jsonp&page=' . $m . '&num=16&target=' . $target . '&source=web_pc';
                $params = [
                    'game' => 'lol',
                    'mission_type' => 'information',
                    'source_link' => $url,
                ];
                $result = $missionModel->getMissionCount($params);//过滤已经采集过的文章

                $result = $result ?? 0;
                if ($result <= 0) {
                    $data = [
                        "asign_to" => 1,
                        "mission_type" => 'information',//资讯
                        "mission_status" => 1,
                        "game" => 'lol',
                        "source" => 'lol_qq',//
                        'title' => '',
                        'source_link' => $url,
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
                $t2 = microtime(true);
                echo '耗时' . round($t2 - $t1, 3) . '秒' . "\n";
            }

        }
        return true;
    }

    //王者荣耀资讯站
    public function insertKplInformation()
    {
        //1761=>新闻,1762=>公告,1763=>活动,1764=>赛事,1765=>攻略
        $targetItem = [
            1761, 1762, 1763, 1764, 1765
        ];
        foreach ($targetItem as $val) {
            $type = $val;
            $missionModel = new MissionModel();
            $lastPage = 9;
            for ($i = 0; $i <= $lastPage; $i++) {
                $t1 = microtime(true);
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
                        $site_id = $val['iNewsId'] ?? 0;
                        $informationModel = new InformationModel();
                        $informationInfo = $informationModel->getInformationBySiteId($site_id, 'kpl', 'pvp_qq');
                        if (count($informationInfo) <= 0) {
                            $detail_url = 'https://apps.game.qq.com/wmp/v3.1/public/searchNews.php?source=pvpweb_detail&p0=18&id=' . $val['iNewsId'];//攻略
                            $params = [
                                'game' => 'kpl',
                                'mission_type' => 'information',
                                'source_link' => $detail_url,
                            ];
                            $result = $missionModel->getMissionCount($params);
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
                                    'source_link' => $detail_url,
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
                        } else {
                            continue;
                        }

                    }
                }
                $t2 = microtime(true);
                echo '耗时' . round($t2 - $t1, 3) . '秒' . "\n";
            }
        }
        return true;
    }

    //dota2官网资讯
    public function insertDota2Information($gametype)
    {
        $missionModel = new MissionModel();
        $count = 29;
        $cdata = [];
        for ($i = 0; $i <= $count; $i++) {
            $m = $i + 1;
           // $typeList=['news','gamenews','competition','news_update'];
            if($gametype=='news'){
                $url = 'https://www.dota2.com.cn/'.$gametype.'/index' . $m . '.htm';
            }elseif($gametype=='news_update'){
                $url = 'https://www.dota2.com.cn/news/gamepost/'.$gametype.'/index' . $m . '.htm';
            }else{
                $url = 'https://www.dota2.com.cn/news/'.$gametype.'/index' . $m . '.htm';
            }

            $urlall = QueryList::get($url)->find("#news_lists .panes .active a")->attrs('href')->all();

            if ($urlall) {
                foreach ($urlall as $key => $val) {
                    $title = QueryList::get($url)->find("#news_lists .panes .active a:eq(" . $key . ") .news_msg .title")->text();
                    $remark= QueryList::get($url)->find("#news_lists .panes .active a:eq(" . $key . ") .news_msg .content")->text();
                    $create_time= QueryList::get($url)->find("#news_lists .panes .active a:eq(" . $key . ") .news_msg .date")->text();
                    $logo = QueryList::get($url)->find("#news_lists .panes .active a:eq(" . $key . ") .news_logo img")->attr('src');
                    if(strpos($logo,'https')===false){
                        $logo='https:'.$logo;
                    }
                    $params = [
                        'game' => 'dota2',
                        'mission_type' => 'information',
                        'source_link' => $val,
                    ];
                    $result = $missionModel->getMissionCount($params);
                    //过滤已经采集过的文章
                    $result = $result ?? 0;
                    if($result <=0){
                        $data = [
                            "asign_to" => 1,
                            "mission_type" => 'information',//资讯
                            "mission_status" => 1,
                            "game" => 'dota2',
                            "source" => 'gamedota2',//
                            'title' => $title ?? '',
                            'source_link' => $val,
                            "detail" => json_encode(
                                [
                                    "url" => $val,
                                    "game" => 'dota2',//dota2
                                    "source" => 'gamedota2',//资讯
                                    'type' => 'dota2',
                                    'remark'=>$remark,
                                    'create_time'=>$create_time,
                                    'logo'=>$logo,
                                    'type'=>$gametype,//1=>gamenews
                                    'author'=>'官网资讯'
                                ]
                            ),
                        ];
                        $insert = (new oMission())->insertMission($data);
                    }


                }
            }
        }
        return true;
    }

    //官网攻略
    public function insertDota2Raiders($gametype){
        $missionModel = new MissionModel();
        $count = 29;
        $cdata = [];
        for ($i = 0; $i <= $count; $i++) {
            $m = $i + 1;

            if($gametype=='raiders'){
                $url = 'https://www.dota2.com.cn/'.$gametype.'/index' . $m . '.htm#hd_li';
            }else{
                $url = 'https://www.dota2.com.cn/raiders/'.$gametype.'/index' . $m . '.htm#hd_li';
            }

            $urlall = QueryList::get($url)->find(".content .hd_li .img_left a")->attrs('href')->all();
           // print_r($urlall);exit;

            if ($urlall) {
                foreach ($urlall as $key => $val) {
                    $title = QueryList::get($url)->find(".content .hd_li li:eq(" . $key . ") .title_right .enter_title")->text();
                    $remark= QueryList::get($url)->find(".content .hd_li li:eq(" . $key . ") .title_right p")->text();
                    $create_time= '';
                    $logo = QueryList::get($url)->find(".content .hd_li li:eq(" . $key . ") .img_left  img")->attr('src');
                    if(strpos($logo,'https')===false){
                        $logo='https:'.$logo;
                    }
                    $params = [
                        'game' => 'dota2',
                        'mission_type' => 'information',
                        'source_link' => $val,
                    ];
                    $result = $missionModel->getMissionCount($params);
                    //过滤已经采集过的文章
                    $result = $result ?? 0;
                    if($result <=0){
                        $data = [
                            "asign_to" => 1,
                            "mission_type" => 'information',//资讯
                            "mission_status" => 1,
                            "game" => 'dota2',
                            "source" => 'gamedota2',//
                            'title' => $title ?? '',
                            'source_link' => $val,
                            "detail" => json_encode(
                                [
                                    "url" => $val,
                                    "game" => 'dota2',//dota2
                                    "source" => 'gamedota2',//资讯
                                    'type' => 'dota2',
                                    'remark'=>$remark,
                                    'create_time'=>$create_time,
                                    'logo'=>$logo,
                                    'type'=>'raiders',//1=>gamenews
                                    'author'=>'官网攻略'
                                ]
                            ),
                        ];
                        $insert = (new oMission())->insertMission($data);
                    }


                }
            }
        }
        return true;

    }

}
