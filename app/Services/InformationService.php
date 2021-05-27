<?php

namespace App\Services;

use App\Libs\AjaxRequest;
use App\Libs\Baidu\AipNlp;
use App\Libs\ClientServices;
use App\Models\Admin\Site;
use App\Models\CollectResultModel;
use App\Models\InformationModel;
use App\Models\MissionModel;
use App\Services\Data\RedisService;
use App\Services\MissionService as oMission;
use Illuminate\Support\Facades\DB;
use QL\QueryList;

class InformationService
{
    const MISSION_REPEAT = 100;//调用重复多少条数量就终止

    public function insertData($game, $force = 0)
    {
        switch ($game) {
            case "lol":
                $this->insertLolInformation($force);//lol官网资讯采集
                break;
            case "kpl":
                $this->insertKplInformation($force);//kpl官网资讯采集
                break;
            case "dota2":
                $typeList = ['news', 'gamenews', 'competition', 'news_update'];
                $raidersList = ['raiders', 'newer', 'step', 'skill'];
                foreach ($typeList as $v1) {
                    $this->insertDota2Information($v1, $force);//dota2资讯采集
                }
                foreach ($raidersList as $v2) {
                    $this->insertDota2Raiders($v2, $force);//攻略采集
                }


                break;
            case "csgo":

                break;
            default:

                break;
        }
        $this->insertWanplusVideo($game, $force);//资讯视频采集方法
        return 'finish';
    }

    //英雄联盟资讯采集
    public function insertLolInformation($force = 0)
    {
        //23=>'综合',24=>'公告',25=>'赛事',27=>'攻略',28=>'社区'

        $targetItem = [
            23, 24, 25, 27, 28
        ];
        $informationModel = new InformationModel();
        $missionModel = new MissionModel();
        $total = 0;
        $mission_repeat = 0;
        foreach ($targetItem as $val) {
            $target = $val;

            $lastPage = 9;//采集最新的50页数据
            for ($i = 0; $i <= $lastPage; $i++) {
                $t1 = microtime(true);
                $m = $i + 1;
                $url = 'https://apps.game.qq.com/cmc/zmMcnTargetContentList?r0=jsonp&page=' . $m . '&num=16&target=' . $target . '&source=web_pc';
                $information_list = curl_get($url);//获取详情接口信息
                $information_list = $information_list['data']['result'] ?? [];//每一页列表数据
                if (count($information_list) > 0) {
                    foreach ($information_list as $informationInfo) {
                        $site_id = intval($informationInfo['iDocID']) ?? 0;//原地址新闻id

                        if ($site_id > 0) {
                            $detail_url = 'https://apps.game.qq.com/cmc/zmMcnContentInfo?r0=jsonp&source=web_pc&type=0&docid=' . $informationInfo['iDocID'];
                            $detail_data = curl_get($detail_url);//获取详情接口信息
                            $detail_data = $detail_data['data']['result'] ?? [];

                            if ((count($detail_data) > 1 && strlen($detail_data['sContent']) > 150)) {//判断内容长度
                                //　强制爬取
                                if ($force == 1) {
                                    $toGet = 1;
                                } elseif ($force == 0) {
                                    //获取资讯信息
                                    $site_id = $informationInfo['iDocID'] ?? 0;
                                    $informationInfo = $informationModel->getInformationBySiteId($site_id, 'lol', 'lol_qq');

                                    //找到
                                    if (isset($informationInfo['site_id'])) {
                                        $toGet = 0;
                                        $mission_repeat++;
                                        echo "lol-information_exits-site_id::" . $site_id . "\n";
                                        if ($mission_repeat >= self::MISSION_REPEAT) {
                                            echo "information-lol-lol_qq重复任务超过" . self::MISSION_REPEAT . "次，任务终止\n";
                                            return;
                                        }
                                    } else {
                                        $mission_repeat = 0;
                                        $toGet = 1;
                                    }
                                }
                                if ($toGet == 1) {
                                    $detail_data['sCreated'] = date("Y-m-d H:i:s");
                                    $detail_data['target'] = $target;
                                    $detail_data['source'] = 'lol_qq';//资讯
                                    $detail_data['game'] = 'lol';
                                    $detail_data['url'] = $detail_url ?? '';
                                    $params = [
                                        'game' => 'lol',
                                        'mission_type' => 'information',
                                        'source_link' => $detail_url,
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
                                            'title' => $detail_data['sTitle'] ?? '',
                                            'source_link' => $detail_url,
                                            "detail" => json_encode($detail_data),
                                        ];
                                        $insert = $missionModel->insertMission($data);
                                        $mission_repeat = 0;
                                        echo "lol-information-insert:" . $insert . ' lenth:' . strlen($data['detail']) . "\n";
                                    } else {
                                        $mission_repeat++;//重复记录加一
                                        echo "mission-exits-lol-lol_qq-information" . "\n";//表示任务表存在记录，跳出继续
                                        if ($mission_repeat >= self::MISSION_REPEAT) {
                                            echo "information-lol-lol_qq重复任务超过" . self::MISSION_REPEAT . "次，任务终止\n";
                                            return;
                                        }
                                    }

                                }

                            }
                        }
                    }

                }

            }

        }
        return true;
    }

    //王者荣耀资讯站
    public function insertKplInformation($force = 0)
    {
        //1761=>新闻,1762=>公告,1763=>活动,1764=>赛事,1765=>攻略
        $targetItem = [
            1761, 1762, 1763, 1764, 1765
        ];
        $informationModel = new InformationModel();
        $missionModel = new MissionModel();
        $mission_repeat = 0;
        foreach ($targetItem as $val) {
            $type = $val;

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
                        $site_id = intval($val['iNewsId']) ?? 0;//原地址新闻id

                        if ($site_id > 0) {
                            //　强制爬取
                            if ($force == 1) {
                                $toGet = 1;
                            } elseif ($force == 0) {
                                //获取当前比赛数据
                                $informationInfo = $informationModel->getInformationBySiteId($site_id, 'kpl', 'pvp_qq');
                                //找到
                                if (isset($informationInfo['site_id'])) {
                                    $toGet = 0;
                                    $mission_repeat++;
                                    echo "exits-information_site_id:" . $site_id . "\n";
                                    if ($mission_repeat >= self::MISSION_REPEAT) {
                                        echo "information-kpl-pvp_qq重复任务超过" . self::MISSION_REPEAT . "次，任务终止\n";
                                        return;
                                    }
                                } else {
                                    $mission_repeat = 0;
                                    $toGet = 1;
                                }
                            }


                            $informationInfo = $informationInfo ?? [];
                            if ($toGet == 1) {//资讯在数据库不存在
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
                                    $insert = $missionModel->insertMission($data);
                                    $mission_repeat = 0;
                                    echo "information-kpl-insert:" . $insert . ' lenth:' . strlen($data['detail']) . "\n";
                                } else {
                                    $mission_repeat++;//重复记录加一
                                    echo "exits-mission-information-kpl-source_link" . $detail_url . "\n";//表示Mission表 记录存在,跳出继续
                                    if ($mission_repeat >= self::MISSION_REPEAT) {
                                        echo "information-kpl-pvp_qq重复任务超过" . self::MISSION_REPEAT . "次，任务终止\n";
                                        return;
                                    }
                                }
                            }
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
    public function insertDota2Information($gametype, $force = 0)
    {
        $missionModel = new MissionModel();
        $informationModel = new InformationModel();
        $count = 29;
        $cdata = [];
        $mission_repeat = 0;
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
                        //　强制爬取
                        if ($force == 1) {
                            $toGet = 1;
                        } elseif ($force == 0) {
                            //获取当前比赛数据
                            $informationInfo = $informationModel->getInformationBySiteId($site_id, 'dota2', 'gamedota2');
                            //找到
                            if (isset($informationInfo['site_id'])) {
                                $toGet = 0;
                                $mission_repeat++;
                                echo "information-dota2-exits-site:" . $site_id . "\n";
                                if ($mission_repeat >= self::MISSION_REPEAT) {
                                    echo "information-dota2-gamedota2重复任务超过" . self::MISSION_REPEAT . "次，任务终止\n";
                                    return;
                                }
                            } else {
                                $mission_repeat = 0;
                                $toGet = 1;
                            }
                        }
                        if ($toGet == 1) {
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
                                $insert = $missionModel->insertMission($data);
                                $mission_repeat = 0;
                                echo "insert:" . $insert . ' lenth:' . strlen($data['detail']) . "\n";
                            } else {
                                $mission_repeat++;//重复记录加一
                                echo "mission-exits-dota2-source_link:" . $val . "-type" . $gametype . "\n";//表示Mission表 记录存在
                                if ($mission_repeat >= self::MISSION_REPEAT) {
                                    echo "information-dota2-gamedota2重复任务超过" . self::MISSION_REPEAT . "次，任务终止\n";
                                    return;
                                }
                            }
                        }
                    } else {
                        echo 'site:' . $site_id;
                    }

                }
            }
        }
        return true;
    }

    //官网攻略
    public function insertDota2Raiders($gametype, $force = 0)
    {
        $missionModel = new MissionModel();
        $informationModel = new InformationModel();
        $countPage = 29;
        $cdata = [];
        $mission_repeat = 0;
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

                        //　强制爬取
                        if ($force == 1) {
                            $toGet = 1;
                        } elseif ($force == 0) {
                            //获取当前比赛数据
                            $informationInfo = $informationModel->getInformationBySiteId($site_id, 'dota2', 'gamedota2');
                            //找到
                            if (isset($informationInfo['site_id'])) {
                                $toGet = 0;
                                $mission_repeat++;
                                echo "information-dota2-exits-site:" . $site_id . "\n";
                                if ($mission_repeat >= self::MISSION_REPEAT) {
                                    echo "information-dota2-gamedota2-raiders重复任务超过" . self::MISSION_REPEAT . "次，任务终止\n";
                                    return;
                                }
                            } else {
                                $mission_repeat = 0;
                                $toGet = 1;
                            }
                        }
                        if ($toGet == 1) {
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
                                $insert = $missionModel->insertMission($data);
                                $mission_repeat = 0;
                                echo "dota2-gamedota2-raiders-insert:" . $insert . ' lenth:' . strlen($data['detail']) . "\n";
                            } else {
                                $mission_repeat++;
                                echo "dota2-gamedota2-raiders-information-mission-exits" . "\n";//表示Mission 记录存在
                                if ($mission_repeat >= self::MISSION_REPEAT) {
                                    echo "information-dota2-gamedota2-raiders重复任务超过" . self::MISSION_REPEAT . "次，任务终止\n";
                                    return;
                                }
                            }
                        }
                    } else {
                        echo 'site:' . $site_id;
                    }

                }
            }
        }
        return true;

    }

    //视频采集
    public function insertWanplusVideo($game, $force = 0)
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
        $mission_repeat = 0;
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
                    //　强制爬取
                    if ($force == 1) {
                        $toGet = 1;
                    } elseif ($force == 0) {
                        //获取当前比赛数据
                        $informationInfo = $informationModel->getInformationBySiteId($val['id'], $game, 'wanplus');
                        //找到
                        if (isset($informationInfo['site_id'])) {
                            $toGet = 0;
                            $mission_repeat++;
                            echo $game . "-information-wanplus-video-exits-site_id:" . $val['id'] . "\n";
                            if ($mission_repeat >= self::MISSION_REPEAT) {
                                echo "重复任务超过" . self::MISSION_REPEAT . "次，任务终止\n";
                                return;
                            }
                        } else {
                            $mission_repeat = 0;
                            $toGet = 1;
                        }
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

                    if ($toGet == 1) {
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
                            $insert = $missionModel->insertMission($data);
                            $mission_repeat = 0;
                            echo $game . "-information-wanplus-insert:" . $insert . ' lenth:' . strlen($data['detail']) . "\n";
                        } else {
                            //表示Mission表记录已存在，跳出继续
                            $mission_repeat++;//重复记录加一
                            echo $game . "-information-mission-wanplus-video-exist-" . '-source_link:' . $detail['url'] . "\n";
                            if ($mission_repeat >= self::MISSION_REPEAT) {
                                echo "重复任务超过" . self::MISSION_REPEAT . "次，任务终止\n";
                                return;
                            }
                        }
                    }
                }
            }
        }
        return true;
    }

    //更新预发布脚本
    public function unPublishedList()
    {
        $informationModel = new InformationModel();
        $redisService = new RedisService();
        $keywordsService = new KeywordService();
        $siteModel = new Site();
        $client = new AipNlp(config("app.baidu.APP_ID"), config("app.baidu.API_KEY"), config("app.baidu.SECRET_KEY"));
        $informationList = $informationModel->getInformationList(["status" => 3, "fields" => "id,time_to_publish,game,type"]);
        $curTime = time();
        foreach ($informationList as $val) {
            if ((strtotime($val['time_to_publish'])) <= $curTime) {
                echo "start to process:" . $val['id'] . "\n";
                $data['status'] = 1;
                $data['create_time'] = $val['time_to_publish'];
                $rt = $informationModel->updateInformation($val['id'], $data);
                if ($rt) {
                    echo "published:" . $val['id'] . "\n";
                    $keywordsService->processScws($val['id'], $informationModel);
                    $keywordsService->process5118Coreword($val['id'], $informationModel);
                    $keywordsService->processBaiduKeyword($val['id'], $informationModel, $client);
                    if ($val['type'] == 4) {
                        $type = "/strategylist/1/reset";
                    } elseif (in_array($val['type'], [1, 2, 3, 5])) {
                        $type = "/newslist/1/reset";
                    }

                    switch ($val['game']) {
                        case "lol":
                            $id = 1;
                            break;
                        case "kpl":
                            $id = 2;
                            break;
                        /*case "dota2":
                            $id=4;
                            break;
                        */
                    }
                    if ($id > 0) {
                        //请求浏览器刷新缓存
                        $siteInfo = $siteModel->getSiteById($id);
                        $domain = $siteInfo['domain'] ?? '';
                        $url = $domain . $type;
                        $rt = file_get_contents($url);
                        echo $rt . "\n";
                    }
                }
            }

        }
        return true;
    }
    //修复脚本数据


    public function updateInformationRedirect(){
        $informationModel = new InformationModel();
        $data=$informationModel
            ->selectRaw("count('id') as num,site_id")
            ->where('source',"<>","index")
            ->whereRaw('left(site_id,2) =? and length(site_id) =?',[15,7])
            ->where('site_id',"<>","")
            ->where('site_id',"<>",0)
            ->where('redirect','=',0)
            ->groupBy('site_id')
            ->having("num",">",1)
            ->get()->toArray();

        $data=$data?? [];
        if(count($data)>0){
            foreach ($data as $val){
                echo "------------------------------\n";
                echo "site_id:".$val['site_id']."\n";
                $informationResult=$informationModel->selectRaw('id,game')
                    ->where('site_id',"=",$val['site_id'])
                    ->where('redirect','=',0)
                    ->orderBy('id','asc')
                    ->get()->toArray();
                $gameList = [];
                //$gameList=array_column($informationResult,'game');
                foreach ($informationResult as $info)
                {
                    if(!isset($gameList[$info['game']]))
                    {
                        $gameList[$info['game']] = $info['id'];
                    }
                }
                if(isset($gameList["lol"]))
                {
                    $redirect = $gameList["lol"];
                    echo "target:".$redirect."\n";
                    foreach($informationResult as $info)
                    {
                        if($info['id']!= $redirect)
                        {
                            echo "toUpdate:".$info['id'].",redirect:".$redirect;
                            //$rt=0;
                            $rt=$informationModel->updateInformation($info['id'],['redirect'=>$redirect]);
                            if($rt){
                                echo "-success"."\n";
                            }else{
                                echo "-fail"."\n";
                            }
                           // sleep(1);
                        }
                    }
                }
                 // print_r($informationResult);exit;
            }
        }
    }

}
