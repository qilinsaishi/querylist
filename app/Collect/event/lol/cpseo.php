<?php

namespace App\Collect\event\lol;

use QL\QueryList;

class cpseo
{
    protected $data_map =
        [
            'list'=>[
                'game'=>['path'=>"",'default'=>"lol"],//游戏
                'home_score'=>['path'=>"",'default'=>0],//主队得分
                'away_score'=>['path'=>"",'default'=>0],//客队得分
                'home_id'=>['path'=>"home_id",'default'=>0],//主队id
                'away_id'=>['path'=>"away_id",'default'=>0],//客队id
                'logo'=>['path'=>"",'default'=>""],//logo
                //"match_id"=>['path'=>"id",'default'=>""],//比赛唯一ID
                "tournament_id"=>['path'=>"tournament_id",'default'=>""],//赛事唯一ID
                "extra"=>['path'=>"extra",'default'=>[]],//额外信息
                "start_time"=>['path'=>"start_time",'default'=>[]],//开始时间
            ],
            'tournament'=>[
                'game'=>['path'=>"",'default'=>"lol"],//游戏
                //'tournament_id'=>['path'=>"id",'default'=>''],//赛事ID
                'tournament_name'=>['path'=>"title",'default'=>''],//赛事名称
                'start_time'=>['path'=>"start_time",'default'=>0],//开始时间
                'end_time'=>['path'=>"end_time",'default'=>0],//开始时间
                'logo'=>['path'=>"logo",'default'=>''],//logo
                'pic'=>['path'=>"",'default'=>''],//关联图片
                'game_logo'=>['path'=>"",'default'=>''],//关联游戏图片
            ],
        ];


    public function collect($arr)
    {
        $cdata = [];
        $url = $arr['detail']['url'] ?? '';
        $res = $this->getEventData($url);


        if (!empty($res)) {

            $cdata = [
                'mission_id' => $arr['mission_id'],//任务id
                'content' => is_array($res) ? json_encode($res) : [],
                'game' => $arr['game'],//游戏类型
                'source_link' => $url,
                'title' => $arr['detail']['title'] ?? '',
                'mission_type' => $arr['mission_type'],
                'source' => $arr['source'],
                'status' => 1,
                'update_time' => date("Y-m-d H:i:s")
            ];
        }
        return $cdata;
    }

    public function process($arr,$teamList = [],$tournamentList = [])
    {
        /**
         * //赛事
         * baseInfo{
         * "game_id:1=>lol,2=>kpl,3=>dota2
         * }
         * pkTeam{
         * type:类型
         * date_2:日期
         * opponents_dec:战队信息
         * opponents_img:http://www.2cpseo.com/storage/match/December2020/e9d75350c7c557aea20d69823dc5cb58.png  //战队图片
         * pk时间：dtime
         * }
         */
        if($teamList == [] || $tournamentList == [])
        {
            return ;
        }
        else
        {
            $tournamentList = array_combine(array_column($tournamentList,"tournament_name"),array_column($tournamentList,"tournament_id"));
            $teamList = array_combine(array_column($teamList,"team_name"),array_column($teamList,"team_id"));
            $data = [];
            if(isset($tournamentList[$arr['content']['baseInfo']['title']]))
            {
                foreach($arr['content']['pkTeam'] as $key => $match)
                {
                    foreach($match['teamInfo'] as $k => $matchDetail)
                    {
                        if(isset($teamList[$matchDetail['opponents_dec'][0]]) && isset($teamList[$matchDetail['opponents_dec'][1]]))
                        {
                            $data[] = getDataFromMapping($this->data_map['list'],[
                                'tournament_id'=>$tournamentList[$arr['content']['baseInfo']['title']],
                                'home_id' => $teamList[$matchDetail['opponents_dec'][0]],
                                'away_id' => $teamList[$matchDetail['opponents_dec'][1]],
                                'start_time' => (preg_replace('/([\x80-\xff]*)/i','',$matchDetail['date_2']) ." ".trim(preg_replace('/([\x80-\xff]*)/i','',$matchDetail['dtime']))),
                                'extra'=>['type'=>$match['type']]
                            ]);
                        }
                    }

                }
            }
            return $data;
        }
    }

    public function getEventData($url)
    {
        $ql = QueryList::get($url);
        $logo = $ql->find('.kf_roster_dec img')->attr('src');
        $logo = 'http://www.2cpseo.com' . $logo;
        $wraps = $ql->find('.text_wrap:eq(0) .text_2 p')->text();
        $wraps = explode("\n", $wraps);
        if ($wraps) {
            foreach ($wraps as $key => $val) {
                if (strpos($val, '英雄联盟：') !== false) {
                    $title = str_replace('英雄联盟：', '', $val);
                }
                if (strpos($val, '开始时间：') !== false) {
                    $startTime = str_replace('开始时间：', '', $val);

                }
                if (strpos($val, '结束时间：') !== false) {
                    $endTime = str_replace('结束时间：', '', $val);
                }

            }
        }

        $baseInfo = [
            'logo' => $logo,
            'title' => $title ?? '',
            'start_time' => $startTime ?? '',
            'end_time' => $endTime ?? '',
            'game_id' => 1,//game: 1表示lol
        ];

        $tapType = $ql->find('.tranding_tab .nav-tabs li')->texts()->all();
        $pkTeam = [];
        if (!empty($tapType)) {
            foreach ($tapType as $key => &$val) {
                $pkTeam[$key]['type'] = $val;
                $pkTeam[$key]['teamInfo'] = $ql->rules([
                    'date_2' => ['.date_2', 'text'],
                    'opponents_dec' => ['.kf_opponents_dec  h6', 'texts'],
                    'opponents_img' => ['.kf_opponents_dec  span ', 'htmls'],
                    'dtime' => ['.kf_opponents_gols  p', 'text']
                ])->range('#home' . ($key + 1) . ' li')->queryData();
            }
        }
        $res['baseInfo'] = $baseInfo ?? [];//赛事基本
        $res['pkTeam'] = $pkTeam ?? [];//pk战队
        return $res;
    }
    public function getTeams4Match($arr)
    {
        $teamList = [];
        foreach($arr['content']['pkTeam'] as $key => $match)
        {
            foreach($match["teamInfo"] as $k => $match_team)
            {
                $teamList = array_merge($teamList,array_values($match_team['opponents_dec']));
            }
        }
        return $teamList;
    }
    public function getTournament4Match($arr)
    {
        $arr['content']['baseInfo']['start_time'] = strtotime($arr['content']['baseInfo']['start_time']);
        $arr['content']['baseInfo']['end_time'] = strtotime($arr['content']['baseInfo']['end_time']);
        $arr['content']['baseInfo']['logo'] = getImage($arr['content']['baseInfo']['logo']);
        return getDataFromMapping($this->data_map['tournament'],$arr['content']['baseInfo']);
    }
}
