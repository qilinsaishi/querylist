<?php

namespace App\Collect\match\dota2;

use App\Libs\ClientServices;
use http\Message\Body;
use QL\QueryList;

class shangniu
{
    protected $data_map =
        [
            'tournament' => [
                'game' => ['path' => "", 'default' => "dota2"],//游戏
                'tournament_id' => ['path' => "tournamentId", 'default' => ''],//赛事ID
                'tournament_name' => ['path' => "tournamentName", 'default' => ''],//赛事名称
                'start_time' => ['path' => "startTime", 'default' => 0],//开始时间
                'end_time' => ['path' => "endTime", 'default' => 0],//开始时间
                'logo' => ['path' => "tournamentLogo", 'default' => ''],//logo
                'status' => ['path' => "status", 'default' => '0'],//关联图片
                'game_logo' => ['path' => "", 'default' => ''],//关联游戏图片
            ],

            'list' => [
                'match_id' => ['path' => "id", 'default' => 0],//比赛唯一ID
                'match_status' => ['path' => "status", 'default' => 0],//比赛状态
                'game' => ['path' => "", 'default' => "dota2"],//游戏
                'home_score' => ['path' => "homeScore", 'default' => 0],//主队得分
                'away_score' => ['path' => "awayScore", 'default' => 0],//客队得分
                'home_id' => ['path' => "homeId", 'default' => 0],//主队id
                'away_id' => ['path' => "awayId", 'default' => 0],//客队id
                'logo' => ['path' => "", 'default' => ""],//logo
                'home_logo' => ['path' => "homeLogo", 'default' => ""],//主队logo
                'away_logo' => ['path' => "awayLogo", 'default' => ""],//客队logo
                'home_name' => ['path' => "homeName", 'default' => ''],//主队名称
                'away_name' => ['path' => "awayName", 'default' => ''],//主队名称
                "tournament_id" => ['path' => "tournamentId", 'default' => ""],//赛事唯一ID
                "start_time" => ['path' => "matchTime", 'default' => ''],
                "match_pre" => ['path' => "match_pre", 'default' => []],//赛前数据
                "match_data" => ['path' => "match_data", 'default' => []],//赛事数据
                "game_bo" => ['path' => "box", 'default' => ''],
                'round_detailed' => ['path' => "round_detailed", 'default' => 0],//客队id
                'next_try' => ['path' => "next_try", 'default' => 0],//客队id
                'try' => ['path' => "try", 'default' => 0],//轮次
                'home_display' => ['path' => "home_display", 'default' => 0],//主队要求显示
                'away_display' => ['path' => "away_display", 'default' => 0],//客队要求显示

            ]
        ];

    public function collect($arr)
    {
        $client = new ClientServices();
        $cdata = [];
        $res = $arr['detail'] ?? [];
        $type = $arr['detail']['type'] ?? '';
        $act = $arr['detail']['act'] ?? 'insert';
        $try = $res['try'] ?? 0;
        $res['round_detailed'] = 0;


        if ($type == 'match') {//赛程
            //=============================赛前数据=====================================
            $refererUrl = 'https://www.shangniu.cn/esports/dota-live-' . $res['id'] . '.html?tab=1';
            $headers = ['referer' => $refererUrl];
            //战队信息分析
            $teamBaseUrl = 'https://www.shangniu.cn/api/game/user/match/getMatchProspect?matchId=' . $res['id'] . '&gameType=dota';

            $teamBaseData = $client->curlGet($teamBaseUrl, [], $headers);
            $teamBaseData = $teamBaseData['body'] ?? [];
            //队员信息
            $playerBaseUrl = 'https://www.shangniu.cn/api/game/user/player/getPlayerStatByMatchId?matchId=' . $res['id'] . '&gameType=dota';
            $playerStatData = $client->curlGet($playerBaseUrl, [], $headers);
            $playerStatData = $playerStatData['body'] ?? [];
            //英雄信息
            $heroBaseUrl = 'https://www.shangniu.cn/api/game/user/hero/getHeroStat?matchId=' . $res['id'] . '&gameType=dota';
            $heroStatData = $client->curlGet($heroBaseUrl, [], $headers);
            $heroStatData = $heroStatData['body'] ?? [];
            $res['match_pre'] = [
                'teamBaseData' => $teamBaseData,
                'playerStatData' => $playerStatData,
                'heroStatData' => $heroStatData
            ];
            //=============================赛前数据=====================================
            if ($act != 'update') {
                $res['matchTime'] = date("Y-m-d H:i:s", substr($res['matchTime'], 0, -3));

            }

            //=============================比赛数据=====================================
            $matchData = [];
            $matchLiveUrl = 'https://www.shangniu.cn/api/game/user/match/getMatchLive?gameType=dota&matchId=' . $res['id'] . '&tournamentId=' . $res['tournamentId'];
            $matchDiveData = $client->curlGet($matchLiveUrl, [], $headers);
            $matchDiveData = $matchDiveData['body'] ?? [];
            if ($act == 'update') {
                $collect_url='https://www.shangniu.cn/esports/dota-live-'.$res['id'] ;
                $qt=QueryList::get($collect_url);
                $homeUrl=$qt->find('.games-header-style-new .score a:eq(0)')->attr('href');
                $awayUrl=$qt->find('.games-header-style-new .score a:eq(1)')->attr('href');
                $res['homeId']=str_replace(array('/esports/dota-team-','.html'),'',$homeUrl);
                $res['awayId']=str_replace(array('/esports/dota-team-','.html'),'',$awayUrl);
                $res['homeName']=$qt->find('.games-header-style-new .score .team-name:eq(0)')->text();
                $res['awayName']=$qt->find('.games-header-style-new .score .team-name:eq(1)')->text();
                $res['matchTime']=$qt->find('.games-header-style-new .score .time-status .date')->text();
                $res['homeScore']=$qt->find('.games-header-style-new .score .team-score:eq(0)')->text();
                $res['awayScore']=$qt->find('.games-header-style-new .score .team-score:eq(1)')->text();

                if (count($matchDiveData) > 0) {

                    if ($matchDiveData['box'] == $matchDiveData['boxNum']) {
                        $res['round_detailed'] = 1;
                    }

                    if ($matchDiveData['status'] != null && $matchDiveData['status'] > $res['status']) {
                        $res['status'] = $matchDiveData['status'];
                    }

                }

            }

            if (isset($matchDiveData['boxNum']) && $matchDiveData['boxNum'] > 0) {
                $matchData[$matchDiveData['boxNum']] = $matchDiveData;
                //局数
                for ($boxNum = $matchDiveData['boxNum'] - 1; $boxNum > 0; $boxNum--) {
                    $matchLiveBoxNumUrl = 'https://www.shangniu.cn/api/game/user/match/getMatchLive?gameType=dota&matchId=' . $res['id'] . '&tournamentId=' . $res['tournamentId'] . '&boxNum=' . $boxNum;
                    $matchDiveBoxNumData = $client->curlGet($matchLiveBoxNumUrl, [], $headers);
                    $matchDiveBoxNumData = $matchDiveBoxNumData['body'] ?? [];
                    $matchData[$boxNum] = $matchDiveBoxNumData;
                    echo $matchLiveBoxNumUrl . "\n";
                }

            }

            if ($act == 'update' && $res['round_detailed'] == 0) {
                $currentTime = time();

                if ($currentTime < strtotime($res['matchTime']))//比赛尚未开始
                {
                    //每次推后2小时
                    $res['next_try'] = 2 * 3600 + $res['next_try'];
                    //不加重试次数
                } else {
                    //每次推后4小时
                    $res['next_try'] = 4 * 3600 + $res['next_try'];
                    $try++;
                }
                $res['try'] = $try;
                echo 'try:' . $try . "\n";
                echo 'next_try:' . $res['next_try'] . "\n";
            } else {
                $res['next_try'] = strtotime($res['matchTime']) - 3 * 86400;
                $res['try'] = 0;
            }

            $res['matchData'] = $matchData;
            //=============================比赛数据=====================================

        } else {//赛事
            if ($res['status'] == 0) {
                $res['status'] = 4;
            }
            if ($res['endTime'] == null) {
                $res['endTime'] = 0;
            }
        }
        if (count($res) > 0) {
            //处理战队采集数据
            $cdata = [
                'mission_id' => $arr['mission_id'],
                'content' => json_encode($res),
                'game' => $arr['game'],
                'source_link' => $arr['source_link'],
                'title' => $arr['title'] ?? '',
                'mission_type' => $arr['mission_type'],
                'source' => $arr['source'],
                'status' => 1,
            ];

        }
        return $cdata;
    }

    public function process($arr)
    {

        $redis = app("redis.connection");
        $data = ['tournament' => [], 'match_list' => []];
        if ($arr['content']['type'] == "tournament") {
            $arr['content']['startTime'] = strtotime(date('Y-m-d H:i:s', $arr['content']['startTime'])) ?? 0;
            $arr['content']['endTime'] = strtotime(date('Y-m-d H:i:s', $arr['content']['endTime'])) ?? 0;
            $arr['content']['tournamentLogo'] = (isset($arr['content']['tournamentLogo']) && $arr['content']['tournamentLogo'] != '') ? getImage($arr['content']['tournamentLogo'], $redis) : '';
            $data['tournament'][] = getDataFromMapping($this->data_map['tournament'], $arr['content']);

        } else {
            $currentKeyList = array_column($this->data_map['list'], 'path');
            $keyList = array_keys($arr['content']);
            $arr['content']['match_data'] = [];
            $arr['content']['matchTime'] = isset($arr['content']['matchTime']) ? $arr['content']['matchTime'] : date("Y-m-d H:i:s", $arr['content']['matchTime']);
            $arr['content']['homeLogo'] = isset($arr['content']['homeLogo']) ? getImage($arr['content']['homeLogo'], $redis) : '';
            $arr['content']['awayLogo'] = isset($arr['content']['awayLogo']) ? getImage($arr['content']['awayLogo'], $redis) : '';
            foreach ($keyList as $key) {
                if (!in_array($key, $currentKeyList)) {
                    $arr['content']['match_data'][$key] = $arr['content'][$key];
                    unset($arr['content'][$key]);
                }
            }

            $arr['content']['match_pre'] = $this->processImg($arr['content']['match_pre'], $redis);
            if (isset($arr['content']['match_data']) && count($arr['content']['match_data']) > 0) {
                $arr['content']['match_data'] = $this->processImg($arr['content']['match_data'], $redis);
            }

            $data['match_list'][] = getDataFromMapping($this->data_map['list'], $arr['content']);
        }
        return $data;
    }

    public function processImg($arr, $redis = null)
    {
        if (is_null($redis)) {
            $redis = app("redis.connection");
        }
        foreach ($arr as $key => $value) {
            if (is_array($value)) {
                $arr[$key] = $this->processImg($value, $redis);
            } else {
                $arr[$key] = checkImg($value, $redis);
            }
        }
        return $arr;
    }
}
