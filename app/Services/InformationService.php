<?php

namespace App\Services;

use App\Libs\AjaxRequest;
use App\Libs\ClientServices;
use App\Models\CollectResultModel;
use App\Models\InformationModel;
use App\Models\MissionModel;
use App\Services\MissionService as oMission;
use QL\QueryList;

class InformationService
{
    public function insertData($game)
    {
        switch ($game) {
            case "lol":
                $this->insertWanplusVideo($game);
                $this->insertLolInformation();
                break;
            case "kpl":
                $this->insertWanplusVideo($game);
                $this->insertKplInformation();
                break;
            case "dota2":
                $typeList = ['news', 'gamenews', 'competition', 'news_update'];
                $raidersList = ['raiders', 'newer', 'step', 'skill'];
                foreach ($typeList as $v1) {
                    $this->insertDota2Information($v1);
                }
                foreach ($raidersList as $v2) {
                    $this->insertDota2Raiders($v2);
                }
                $this->insertWanplusVideo($val);

                break;
            case "csgo":

                break;
            default:

                break;
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
                if ($result == 0) {//表示任务表不存在记录，则插入数据
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
                    echo "lol-information-insert:" . $insert . ' lenth:' . strlen($data['detail']) . "\n";
                } else {
                    echo "mission-exits-lol-lol_qq-information" . "\n";//表示任务表存在记录，跳出继续
                    continue;
                }
                $t2 = microtime(true);
                //echo '耗时' . round($t2 - $t1, 3) . '秒' . "\n";
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
                    $pageData = curl_get($url);//获取每一页的数据
                } else {
                    //攻略
                    $client = new ClientServices();
                    $url = 'https://apps.game.qq.com/wmp/v3.1/?p0=18&p1=searchNewsKeywordsList&page=' . $m . '&pagesize=15&order=sIdxTime';
                    $refeerer = 'https://pvp.qq.com/web201605/searchResult.shtml';

                    $headers = [
                        'Referer' => $refeerer,
                        'Accept' => 'application/json',
                    ];
                    //获取每一页的数据
                    $pageData = $client->curlGet($url, '', $headers);//攻略
                }

                $cdata = $pageData['msg']['result'] ?? [];
                if (count($cdata) > 0) {//数据不能为空
                    foreach ($cdata as $key => $val) {
                        $site_id = $val['iNewsId'] ?? 0;//原地址新闻id
                        $informationModel = new InformationModel();
                        $informationInfo = $informationModel->getInformationBySiteId($site_id, 'kpl', 'pvp_qq');
                        $informationInfo = $informationInfo ?? [];
                        if (count($informationInfo) == 0) {//资讯在数据库不存在
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
                                echo "insert:" . $insert . ' lenth:' . strlen($data['detail']) . "\n";
                            } else {
                                echo "exits-mission-source_link" . $detail_url . "\n";//表示Mission表 记录存在,跳出继续
                                continue;
                            }
                        } else {
                            echo "exits-site_id" . $site_id . "\n";//表示Information表 记录存在,跳出继续
                            continue;
                        }

                    }
                }
                $t2 = microtime(true);
                //echo '耗时' . round($t2 - $t1, 3) . '秒' . "\n";
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
            if ($gametype == 'news') {
                $url = 'https://www.dota2.com.cn/' . $gametype . '/index' . $m . '.htm';
            } elseif ($gametype == 'news_update') {
                $url = 'https://www.dota2.com.cn/news/gamepost/' . $gametype . '/index' . $m . '.htm';
            } else {
                $url = 'https://www.dota2.com.cn/news/' . $gametype . '/index' . $m . '.htm';
            }

            //判断url是否有效
            $headers = get_headers($url, 1);
            if (!preg_match('/200/', $headers[0])) {
                echo "url:" . $url . "\n";
                continue;
            }

            //获取所有的url下面的所有a链接
            $urlall = QueryList::get($url)->find("#news_lists .panes .active a")->attrs('href')->all();

            if ($urlall) {
                foreach ($urlall as $key => $val) {
                    //获取当前a 标签下面的标题
                    $title = QueryList::get($url)->find("#news_lists .panes .active a:eq(" . $key . ") .news_msg .title")->text();
                    //获取当前a 标签下面的简介
                    $remark = QueryList::get($url)->find("#news_lists .panes .active a:eq(" . $key . ") .news_msg .content")->text();
                    //获取当前a 标签下面的创建时间
                    $create_time = QueryList::get($url)->find("#news_lists .panes .active a:eq(" . $key . ") .news_msg .date")->text();
                    //获取当前a 标签下面的logo
                    $logo = QueryList::get($url)->find("#news_lists .panes .active a:eq(" . $key . ") .news_logo img")->attr('src');
                    if (strpos($logo, 'https') === false) {
                        $logo = 'https:' . $logo;
                    }
                    $params = [
                        'game' => 'dota2',
                        'mission_type' => 'information',
                        'source_link' => $val,
                    ];
                    //获取原来资讯id
                    $site_id = substr($val, strrpos($val, '/') + 1, strlen($val) - 1);
                    if (strpos($site_id, '.html')) {
                        $site_id = rtrim($site_id, '.html');
                        $site_id = intval($site_id);
                    }
                    $site_id = $site_id ?? 0;
                    if ($site_id > 0) {
                        $informationModel = new InformationModel();
                        $informationInfo = $informationModel->getInformationBySiteId($site_id, 'dota2', 'gamedota2');
                        $informationInfo = $informationInfo ?? [];
                        if (count($informationInfo) == 0) {
                            $result = $missionModel->getMissionCount($params);
                            //过滤已经采集过的文章
                            $result = $result ?? 0;
                            if ($result <= 0) {//表示Mission 记录不存在
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
                                            //'type' => 'dota2',
                                            'remark' => $remark,
                                            'create_time' => $create_time,
                                            'logo' => $logo,
                                            'type' => $gametype,//1=>gamenews
                                            'author' => '官网资讯'
                                        ]
                                    ),
                                ];
                                $insert = (new oMission())->insertMission($data);
                                echo "insert:" . $insert . ' lenth:' . strlen($data['detail']) . "\n";
                            } else {
                                echo "mission-exits-dota2-source_link:" . $val . "-type" . $gametype . "\n";//表示Mission表 记录存在
                                continue;
                            }
                        } else {
                            //表示information表记录已存在，跳出继续
                            echo "exits-dota2-information-type:" . $gametype . '-site_id:' . $site_id . "\n";
                            continue;
                        }
                    } else {
                        echo 'site:' . $site_id;
                        continue;
                    }

                }
            }
        }
        return true;
    }

    //官网攻略
    public function insertDota2Raiders($gametype)
    {
        $missionModel = new MissionModel();
        $countPage = 29;
        $cdata = [];
        for ($i = 0; $i <= $countPage; $i++) {
            $m = $i + 1;
            //官网攻略链接
            if ($gametype == 'raiders') {
                $url = 'https://www.dota2.com.cn/' . $gametype . '/index' . $m . '.htm#hd_li';
            } else {
                $url = 'https://www.dota2.com.cn/raiders/' . $gametype . '/index' . $m . '.htm#hd_li';
            }
            //判断url是否有效
            $headers = get_headers($url, 1);
            if (!preg_match('/200/', $headers[0])) {
                echo "url:" . $url . "\n";
                continue;
            }
            //获取所有的url下面的所有a链接
            $urlall = QueryList::get($url)->find(".content .hd_li .img_left a")->attrs('href')->all();

            if (count($urlall) > 0) {//数组不为空
                foreach ($urlall as $key => $val) {
                    //获取当前a 标签下面的标题
                    $title = QueryList::get($url)->find(".content .hd_li li:eq(" . $key . ") .title_right .enter_title")->text();
                    //获取当前a 标签下面的简介
                    $remark = QueryList::get($url)->find(".content .hd_li li:eq(" . $key . ") .title_right p")->text();
                    $create_time = '';
                    //获取当前a 标签下面的图片
                    $logo = QueryList::get($url)->find(".content .hd_li li:eq(" . $key . ") .img_left  img")->attr('src');
                    if (strpos($logo, 'https') === false) {
                        $logo = 'https:' . $logo;
                    }
                    //获取原来资讯id
                    $site_id = substr($val, strrpos($val, '/') + 1, strlen($val) - 1);
                    if (strpos($site_id, '.html')) {
                        $site_id = rtrim($site_id, '.html');
                        $site_id = intval($site_id);
                    }
                    $site_id = $site_id ?? 0;
                    if ($site_id > 0) {
                        $informationModel = new InformationModel();
                        $informationInfo = $informationModel->getInformationBySiteId($site_id, 'dota2', 'gamedota2');
                        $informationInfo = $informationInfo ?? [];
                        if (count($informationInfo) == 0) {
                            $params = [
                                'game' => 'dota2',
                                'mission_type' => 'information',
                                'source_link' => $val,
                            ];
                            $result = $missionModel->getMissionCount($params);
                            //过滤已经采集过的文章
                            $result = $result ?? 0;
                            if ($result == 0) {//表示Mission 记录不存在
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
                                            'remark' => $remark,
                                            'create_time' => $create_time,
                                            'logo' => $logo,
                                            'type' => 'raiders',//1=>gamenews
                                            'author' => '官网攻略'
                                        ]
                                    ),
                                ];
                                $insert = (new oMission())->insertMission($data);
                                echo "dota2-gamedota2-insert:" . $insert . ' lenth:' . strlen($data['detail']) . "\n";
                            } else {
                                echo "dota2-gamedota2-information-mission-exits" . "\n";//表示Mission 记录存在
                                continue;
                            }
                        } else {
                            //表示information表记录已存在，跳出继续
                            echo "exits-dota2-information-type:raiders-site_id:" . $site_id . "\n";
                            continue;
                        }
                    } else {
                        echo 'site:' . $site_id;
                        continue;
                    }

                }
            }
        }
        return true;

    }

    //视频采集
    public function insertWanplusVideo($game)
    {
        $totalpages = 62;
        if ($game == 'dota2') {
            $gametype = 1;

        } elseif ($game == 'lol') {
            $gametype = 2;
        } elseif ($game == 'kpl') {
            $gametype = 6;
        } elseif ($game == 'csgo') {
            $gametype = 4;

        }
        $AjaxModel = new AjaxRequest();
        $missionModel = new MissionModel();
        $informationModel = new InformationModel();
        for ($i = 1; $i <= $totalpages; $i++) {
            $url = 'https://www.wanplus.com/ajax/video/getlist?gametype=' . $gametype . '&page=' . $i . '&totalpages=62&type=video&subject=&subSubject=&sort=new';
            $cdata = $AjaxModel->ajaxGetData($url);//获取视频每一页数据
            $cdata = $cdata['list'] ?? [];
            if (count($cdata) > 0) {
                foreach ($cdata as $val) {
                    if ($game == 'kpl') {//王者荣耀视频详情链接
                        $video_url = 'https://www.wanplus.com/kog/video/' . $val['id'];
                    } else {//除了王者荣耀视频详情链接
                        $video_url = 'https://www.wanplus.com/' . $game . '/video/' . $val['id'];
                    }
                    $detail = [
                        'url' => $video_url,
                        'title' => $val['title'],//标题
                        'author' => $val['anchor'],//作者
                        'create_time' => date("Y-m-d H:i:s", $val['created']),
                        'duration' => $val['duration'],//时长
                        'logo' => $val['img'],//简介
                        'remark' => $val['title'],//简介
                        'game' => $game,//游戏
                        'site_id' => $val['id'],//站点id
                        'source' => 'wanplus',//来源
                        'gametype' => $gametype,//游戏类型
                        'type' => 'video'//资讯类型
                    ];
                    $params1 = [
                        'game' => $game,
                        'mission_type' => 'information',
                        'source_link' => $detail['url'],
                    ];
                    $detail['url'] = $detail['url'] ?? '';
                    $detail['game'] = $game;
                    $detail['source'] = 'wanplus';//来源https://www.wanplus.com
                    $informationInfo = $informationModel->getInformationBySiteId($val['id'], $game, 'wanplus');
                    $informationInfo = $informationInfo ?? [];
                    if (count($informationInfo) == 0) {
                        $result = $missionModel->getMissionCount($params1);//过滤已经采集过的文章
                        $result = $result ?? 0;
                        if ($result == 0) {//表示任务表不存在，则插入数据
                            $data = [
                                "asign_to" => 1,
                                "mission_type" => 'information',//赛事
                                "mission_status" => 1,
                                "game" => $game,
                                "source" => 'wanplus',//
                                'title' => $val['title'] ?? '',
                                'source_link' => $detail['url'],
                                "detail" => json_encode($detail),
                            ];
                            $insert = (new oMission())->insertMission($data);
                            echo $game . "-information-wanplus-insert:" . $insert . ' lenth:' . strlen($data['detail']) . "\n";
                        } else {
                            //表示Mission表记录已存在，跳出继续
                            echo $game . "exist-mission-wanplus" . '-source_link:' . $detail['url'] . "\n";
                            continue;
                        }
                    } else {
                        //表示information表记录已存在，跳出继续
                        echo $game . "exits-information-wanplus:" . '-site_id:' . $val['id'] . "\n";
                        continue;
                    }
                }
            }
        }
        return true;
    }

}
