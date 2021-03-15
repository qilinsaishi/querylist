<?php

namespace App\Collect\match\kpl;

class scoregg
{
    protected $data_map =
        [
            'tournament'=>[
                'game'=>['path'=>"",'default'=>"kpl"],//游戏
                'tournament_id'=>['path'=>"tournamentID",'default'=>''],//赛事ID
                'tournament_name'=>['path'=>"name",'default'=>''],//赛事名称
                'start_time'=>['path'=>"start_time",'default'=>0],//开始时间
                'end_time'=>['path'=>"end_time",'default'=>0],//开始时间
                'logo'=>['path'=>"image_thumb",'default'=>''],//logo
                'pic'=>['path'=>"image_thumb",'default'=>''],//关联图片
                'game_logo'=>['path'=>"",'default'=>''],//关联游戏图片
            ],
            'team'=>[
                'game'=>['path'=>"",'default'=>"kpl"],//游戏
                'site_id'=>['path'=>"site_id",'default'=>0],//队伍ID
                'team_name'=>['path'=>"team_name",'default'=>0],//队伍名称
                'logo'=>['path'=>"logo",'default'=>''],//logo
                'original_source'=>['path'=>"",'default'=>'gamedota2'],//初始来源
                'aka'=>['path'=>"",'default'=>''],//别名
            ],
            'list'=>[
                    'match_id'=>['path'=>"matchID",'default'=>0],//比赛唯一ID
                    'game'=>['path'=>"",'default'=>"kpl"],//游戏
                    'home_score'=>['path'=>"team_a_win",'default'=>0],//主队得分
                    'away_score'=>['path'=>"team_a_win",'default'=>0],//客队得分
                    'home_id'=>['path'=>"teamID_a",'default'=>0],//主队id
                    'away_id'=>['path'=>"teamID_b",'default'=>0],//客队id
                    'logo'=>['path'=>"",'default'=>""],//logo
                    "tournament_id"=>['path'=>"tournamentID",'default'=>""],//赛事唯一ID
                    "extra"=>['path'=>"result_list",'default'=>[]],//额外信息
                    "start_time"=>['path'=>"start_time",'default'=>[]],
            ]
        ];

    public function collect($arr)
    {
        $cdata = [];
        $res = $url = $arr['detail'] ?? [];
        $type = $arr['detail']['type'] ?? '';
        if (!empty($res)) {
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
            //处理战队采集数据

        }

        return $cdata;
    }

    public function process($arr)
    { //status:0表示即将开始，1表示正在进行，2已结结束
        /*
        tournamentID 赛事id
            tournament_name  赛事名称
            tournament_image_thumb 赛事缩略图
            start_time_string: "03-24 16:00"  开始时间
            Best of 3  =>game_count: "3"
            status 状态：//status:0表示即将开始，1表示正在进行，2已结结束
            round_name 赛事下面的一级组
            round_son_name: 组下面的二级组
            matchID 比赛id
            teamID_a
            team_a_name
            team_a_image
            team_a_image_thumb
            team_a_win 比分a
            teamID_b
            team_b_name
            team_b_image
            team_b_image_thumb
            team_b_win 比分b
            result_list =array();有值则包含了队员英雄
            "source":"scoregg",//来源
            "type":"match",//比赛type=tournament 表示赛程
            "game":"lol",//游戏
            "status":"2",//status:0表示即将开始，1表示正在进行，2已结结束
        */
        $data = ['tournament'=>[],'match_list'=>[],'team'=>[]];
        if($arr['content']['type']=="tournament")
        {
            $arr['content']['image_thumb'] = getImage($arr['content']['image_thumb']);
            $arr['content']['start_time'] = strtotime($arr['content']['start_date']);
            $arr['content']['end_time'] = strtotime($arr['content']['start_date'])+86400-1;
            $data['tournament'][] = getDataFromMapping($this->data_map['tournament'],$arr['content']);
        }
        else
        {
            $team = [
                ['site_id'=>$arr['content']['teamID_a'],'team_name'=>$arr['content']['team_a_name'],'logo'=>getImage($arr['content']['team_a_image'])],
                ['site_id'=>$arr['content']['teamID_b'],'team_name'=>$arr['content']['team_b_name'],'logo'=>getImage($arr['content']['team_b_image'])],
            ];
            foreach($team as $key => $teamInfo)
            {
                $team[$key] =  getDataFromMapping($this->data_map['team'],$teamInfo);
            }
            foreach($arr['content']['result_list'] as $key => $data)
            {
                if(isset($data['record_list_a']))
                {
                    unset($arr['content']['result_list'][$key]['record_list_a']);
                }
                if(isset($data['record_list_b']))
                {
                    unset($arr['content']['result_list'][$key]['record_list_b']);
                }
            }
            $arr['content']['start_time'] = date("Y-m-d H:i:s",$arr['content']['start_time']);
            $data['match_list'][] = getDataFromMapping($this->data_map['list'],$arr['content']);
        }
        return $data;
    }
}
