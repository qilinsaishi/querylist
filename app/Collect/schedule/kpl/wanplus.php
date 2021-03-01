<?php

namespace App\Collect\schedule\kpl;

use App\Libs\AjaxRequest;
use QL\QueryList;

class wanplus
{
    protected $data_map =
        [
        ];

    public function collect($arr)
    {
        $url = $arr['detail']['url'] ?? '';
        $res = $this->getMatchData($url);
        if (count($res) == 0) {
            $res['matchInfo']['status'] = '即将开始';
            $res['matchInfo']['event_title'] = $arr['detail']['ename'];
            $res['matchInfo']['event_url'] = $url;
            $res['matchInfo']['match_id'] = 0;
            $res['matchInfo']['match_name'] = '';
            $res['matchInfo']['time'] = date("Y-m-d", strtotime($arr['detail']['relation'])) . ' ' . $arr['detail']['starttime'];
            $res['matchInfo']['match_duration'] = '';
            $res['matchInfo']['teamInfo'] = [
                [
                    'teamid' => $arr['detail']['oneseedid'],
                    'teamalias' => $arr['detail']['oneseedname'],
                    'faction' => 'blue',
                    'team_img' => $arr['detail']['oneicon'],
                    'score' => -1,

                ],
                [
                    'teamid' => $arr['detail']['twoseedid'],
                    'teamalias' => $arr['detail']['twoseedname'],
                    'faction' => 'red',
                    'team_img' => $arr['detail']['twoicon'],
                    'score' => -1,

                ],
            ];

        }
        if (!empty($res)) {
            //处理战队采集数据
            $cdata = [
                'mission_id' => $arr['mission_id'],
                'content' => json_encode($res),
                'game' => $arr['game'],
                'source_link' => $url,
                'title' => $arr['detail']['title'] ?? '',
                'mission_type' => $arr['mission_type'],
                'source' => $arr['source'],
                'status' => 1,
            ];
            //处理战队采集数据

        }
        return $cdata;
    }

    public function process($arr)
    {
        /*"event_title":"2021 LCL 春季赛",
        "event_url":"http://www.wanplus.com/event/1028.html",
        "match_id":"73210",
        "match_name":"GAME 1",
        "status":"已结束",
        "time":"2021-03-01 00:00 BO1",
        "match_duration":"37:03",
          "teamInfo":[
            {
                "teamid":"6979",
                "teamalias":"CC",
                "faction":"blue",
                "team_img":"https://static.wanplus.com/data/lol/team/6979_mid.png",
                "score":"1"
            },
            {
                "teamid":"4433",
                "teamalias":"RoX",
                "faction":"red",
                "team_img":"https://static.wanplus.com/data/lol/team/4433_mid.png",
                "score":"0"
            }
        ],
 特别申明       //一下只有正在开始和已经结束才有的数据

        "teamStatsList":{
        "kills":[//击杀数
            14,
            5
        ],
            "golds":[//金钱数
            71189,
            56826
        ],
            "towerkills":[// //推塔数
            "11",
            "2"
        ],
            "dragonkills":[// //小龙数
            "4",
            "2"
        ],
            "baronkills":[////大龙数
            "2",
            "0"
        ]
        },
         "teamHero":[//英雄
        [
                {
                    "hero_img":"https://static.wanplus.com/data/lol/hero/square/Blitzcrank.png",
                    "teamid":"6979",
                    "en_name":"Blitzcrank"
                },
                {
                    "hero_img":"https://static.wanplus.com/data/lol/hero/square/Ezreal.png",
                    "teamid":"6979",
                    "en_name":"Ezreal"
                },

            ],
            [
                {
                    "hero_img":"https://static.wanplus.com/data/lol/hero/square/Camille.png",
                    "teamid":"4433",
                    "en_name":"Camille"
                },
                {
                    "hero_img":"https://static.wanplus.com/data/lol/hero/square/Gnar.png",
                    "teamid":"4433",
                    "en_name":"Gnar"
                },

            ]
        ],
         "playInfo":[
        [:{
        "player_img":"https://static.wanplus.com/data/lol/player/24638_mid.png",
                    "playername":"NoNholy",
                    "kills":"0",
                    "deaths":"2",
                    "assists":"4",
                    "kda":"2",
                    "gold":"13179",
                    "lasthit":"264",
                    "totalDamageDealtToChampions":6699,
                    "totalDamageTaken":18827,
                    "heroImg":"https://static.wanplus.com/data/lol/hero/square/Sett.png",
                    "skill":[//技能
            "https://static.wanplus.com/data/lol/skill/12.png",
            "https://static.wanplus.com/data/lol/skill/4.png"
        ],
                    "equipImg":[装备
            "https://static.wanplus.com/data/lol/item/11.2.1/6631.png",
            "https://static.wanplus.com/data/lol/item/11.2.1/3193.png",
            "
        ]
                },],
           [:{
        "player_img":"https://static.wanplus.com/data/lol/player/40095_mid.png",
                    "playername":"Gimliques",
                    "kills":"0",
                    "deaths":"2",
                    "assists":"3",
                    "kda":"1",
                    "gold":"11189",
                    "lasthit":"259",
                    "totalDamageDealtToChampions":5989,
                    "totalDamageTaken":19591,
                    "heroImg":"https://static.wanplus.com/data/lol/hero/square/Sion.png",
                    "skill":[//技能
            "https://static.wanplus.com/data/lol/skill/12.png",
            "https://static.wanplus.com/data/lol/skill/4.png"
        ],
                    "equipImg":[//装备
            "https://static.wanplus.com/data/lol/item/11.2.1/3076.png",
            "https://static.wanplus.com/data/lol/item/11.2.1/3143.png",
            "https://static.wanplus.com/data/lol/item/11.2.1/6662.png",
            "https://static.wanplus.com/data/lol/item/11.2.1/3105.png",
            "https://static.wanplus.com/data/lol/item/11.2.1/3047.png",
            "https://static.wanplus.com/data/lol/item/11.2.1/1011.png",
            "https://static.wanplus.com/data/lol/item/11.2.1/3363.png"
        ]
                },]
        ]*/

        var_dump($arr);
    }

    //王者荣耀
    public function getMatchData($url)
    {
        $AjaxModel = new AjaxRequest();
        $ql = QueryList::get($url);
        $event_title = $ql->find('.box h1')->text();
        $event_url = $ql->find('.box h1 a')->attr('href');
        if ($event_url) {
            $event_url = 'http://www.wanplus.com' . $event_url;
        }
        $matchInfo = [];
        $game_matchid = $ql->find('.box .game a')->attr('data-matchid');
        $game_matchname = $ql->find('.box .game a')->text();//
        $data = [];
        if ($event_title) {
            $matchInfo['event_title'] = $event_title;
        }
        if ($event_url) {
            $matchInfo['event_url'] = $event_url;
        }
        if ($game_matchid) {
            $matchInfo['match_id'] = $game_matchid;
        }
        if ($game_matchname) {
            $matchInfo['match_name'] = $game_matchname;
        }
        if (count($matchInfo) > 0) {
            $data['matchInfo'] = $matchInfo;
        }
        $score = $ql->find('.box .team-detail li:eq(1) p')->text();
        $matchInfo['status'] = $ql->find('.box .team-detail li:eq(1) .end')->text();
        $matchInfo['time'] = $ql->find('.box .team-detail li:eq(1) .time')->text();

        if ($game_matchid) {
            $url = 'http://www.wanplus.com/ajax/matchdetail/' . $game_matchid;
            $playData = $AjaxModel->getHistoryMatch($url);
            //期间
            $matchInfo['match_duration'] = $playData['info']['duration'];
            //战队信息
            $playData['info']['oneteam']['team_img'] = "https://static.wanplus.com/data/lol/team/" . $playData['info']['oneteam']['teamid'] . "_mid.png";
            $playData['info']['twoteam']['team_img'] = "https://static.wanplus.com/data/lol/team/" . $playData['info']['twoteam']['teamid'] . "_mid.png";
            $matchInfo['teamInfo'][0] = $playData['info']['oneteam'];
            $matchInfo['teamInfo'][1] = $playData['info']['twoteam'];
            if (strpos($score, ':') !== false) {
                $scores = explode(':', $score);
            }
            $matchInfo['teamInfo'][0]['score'] = $scores[0] ?? 0;
            $matchInfo['teamInfo'][1]['score'] = $scores[1] ?? 0;
            //击杀数
            $matchInfo['teamStatsList']['kills'] = $playData['teamStatsList']['kills'] ?? [];
            //金钱数
            $matchInfo['teamStatsList']['golds'] = $playData['teamStatsList']['golds'] ?? [];
            //推塔数
            $matchInfo['teamStatsList']['towerkills'] = $playData['teamStatsList']['towerkills'] ?? [];
            //小龙数
            $matchInfo['teamStatsList']['dragonkills'] = $playData['teamStatsList']['dragonkills'] ?? [];
            //大龙数
            $matchInfo['teamStatsList']['baronkills'] = $playData['teamStatsList']['baronkills'] ?? [];
            //战队关联英雄
            if (isset($playData['bpList']['bans']) && $playData['bpList']['bans']) {
                foreach ($playData['bpList']['bans'] as $key => &$val) {
                    if ($val) {
                        foreach ($val as $k => &$v) {
                            $v['img_url'] = 'https://static.wanplus.com/data/lol/hero/square/' . $v['cpherokey'] . '.' . $playData['info']['heroImgSuffix'];
                            $matchInfo['teamHero'][$key][$k]['hero_img'] = $v['img_url'];
                            $matchInfo['teamHero'][$key][$k]['teamid'] = $v['teamid'];
                            $matchInfo['teamHero'][$key][$k]['en_name'] = $v['cpherokey'];
                        }
                    }
                }
            }
            //队员
            if (isset($playData['plList']) && $playData['plList']) {
                foreach ($playData['plList'] as $key => $val) {
                    if ($val) {
                        foreach ($val as $k => $v) {//print_r($v);exit;
                            $matchInfo['playInfo'][$key][$k]['player_img'] = "https://static.wanplus.com/data/lol/player/" . $v['playerid'] . "_mid.png";
                            $matchInfo['playInfo'][$key][$k]['playername'] = $v['playername'];
                            //kda
                            $matchInfo['playInfo'][$key][$k]['kills'] = $v['kills'];//杀死
                            $matchInfo['playInfo'][$key][$k]['deaths'] = $v['deaths'];//死亡
                            $matchInfo['playInfo'][$key][$k]['assists'] = $v['assists'];//助攻
                            $matchInfo['playInfo'][$key][$k]['kda'] = $v['kda'];
                            //金钱
                            $matchInfo['playInfo'][$key][$k]['gold'] = $v['gold'];
                            //补刀
                            $matchInfo['playInfo'][$key][$k]['lasthit'] = $v['lasthit'];
                            //输出伤害
                            $matchInfo['playInfo'][$key][$k]['totalDamageDealtToChampions'] = $v['stats']['totalDamageDealtToChampions'];
                            //承受伤害
                            $matchInfo['playInfo'][$key][$k]['totalDamageTaken'] = $v['stats']['totalDamageTaken'];
                            //英雄图片
                            $matchInfo['playInfo'][$key][$k]['heroImg'] = "https://static.wanplus.com/data/lol/hero/square/" . $v['cpherokey'] . '.' . $playData['info']['heroImgSuffix'];
                            $skill = "https://static.wanplus.com/data/lol/skill/" . $v['skill1id'] . ".png";
                            $skill2 = "https://static.wanplus.com/data/lol/skill/" . $v['skill2id'] . ".png";
                            //技能图片
                            $matchInfo['playInfo'][$key][$k]['skill'][0] = $skill ?? '';
                            $matchInfo['playInfo'][$key][$k]['skill'][1] = $skill2 ?? '';
                            //装备图片
                            if (isset($v['itemcache']) && $v['itemcache']) {
                                foreach ($v['itemcache'] as $key1 => &$val) {
                                    $matchInfo['playInfo'][$key][$k]['equipImg'][$key1] = "https://static.wanplus.com/data/lol/item/11.2.1/" . $val . ".png";
                                }
                            }
                        }
                    }
                }
            }
            $data['matchInfo'] = $matchInfo;
        }

        return $data;
    }
}
