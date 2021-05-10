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
                'original_source'=>['path'=>"",'default'=>'scoregg'],//初始来源
                'aka'=>['path'=>"",'default'=>''],//别名
            ],
            'list'=>[
                    'match_id'=>['path'=>"matchID",'default'=>0],//比赛唯一ID
                    'match_status'=>['path'=>"status",'default'=>0],//比赛状态
                    'game'=>['path'=>"",'default'=>"kpl"],//游戏
                    'home_score'=>['path'=>"team_a_win",'default'=>0],//主队得分
                    'away_score'=>['path'=>"team_b_win",'default'=>0],//客队得分
                    'home_id'=>['path'=>"teamID_a",'default'=>0],//主队id
                    'away_id'=>['path'=>"teamID_b",'default'=>0],//客队id
                    'logo'=>['path'=>"",'default'=>""],//logo
                    "round_id"=>['path'=>"roundID",'default'=>""],//轮次唯一ID
                    "tournament_id"=>['path'=>"tournamentID",'default'=>""],//赛事唯一ID
                    "start_time"=>['path'=>"start_time",'default'=>[]],
                    "match_pre"=>['path'=>"match_pre",'default'=>[]],//赛前数据
                    "match_live"=>['path'=>"livedata",'default'=>[]],//赛事进程
                    "match_data"=>['path'=>"match_data",'default'=>[]],//赛事数据
                    "round"=>['path'=>"round_list",'default'=>[]]//轮次数据
            ]
        ];

    public function collect($arr)
    {
        $cdata = [];
        $res =$arr['detail'] ?? [];
        $matchID=$arr['detail']['matchID'] ?? 0;
        $status=$arr['detail']['status'] ?? 0;
        $type = $arr['detail']['type'] ?? '';
        if($type=='match'){//赛程
            //status=0 未开始;status=1表示正在开始，status=2表示已经结束
            //表示赛前分析接口
            $match_pre_url='https://img1.famulei.com/match_pre/'.$matchID.'.json';
            $match_pre=curl_get($match_pre_url);
            if($match_pre['code']==200) {
                $res['match_pre']=$match_pre['data'] ?? [];
            }else{
                $res['match_pre']=[];
            }
            //复盘（正在进行或者已结束）
            if($status !=0){
                $livedata_url='https://img1.famulei.com/lol/livedata/'.$matchID.'.json'.'?_='.msectime();
                $livedata=curl_get($livedata_url);//获取复盘数据接口
                //print_r($livedata);exit;
                if($livedata['code']==200) {
                    $res['livedata']=$livedata['data'] ?? [];
                    if(isset($res['livedata']) && count($res['livedata']) >0){
                        foreach ($res['livedata'] as &$vo){
                            $web_url=$vo['web_url'] ?? '';
                            if($web_url !='' ){//判断url不为空

                                $weblivedata=curl_get($web_url);//获取比赛中的详情数据
                                $vo['info']=$weblivedata['data'] ?? [];
                                unset($vo['web_url']);
                                unset($vo['url']);
                            }else{
                                $vo['info']=[];
                            }

                        }
                    }else{
                        $res['livedata']=[];
                    }
                }else{
                    $res['livedata']=[];
                }
            }else{
                $res['livedata']=[];
            }

            if($res['result_list'] && count($res['result_list'] )>0){
                foreach($res['result_list'] as $key => $result)
                {
                    $microtime =  substr(microtime(false),3,3);
                    $result_data_url='https://img1.famulei.com/match/result/'.$result['resultID'].'.json'.'?_='.time().$microtime;
                    $result_data=curl_get($result_data_url);//获取复盘数据接口
                    if($result_data['code']==200) {
                        $res['result_list'][$key]['detail'] = $result_data['data'];
                    }
                }
            }else{
                $res['result_list']=[];
            }
        }


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
            $currentKeyList = array_column($this->data_map['list'],'path');
            $keyList = array_keys($arr['content']);
            /*
            foreach($keyList as $key)
            {
                if(is_array($arr['content'][$key]))
                {
                    echo "key:".$key."\n";
                    $subKeyList = array_keys($arr['content'][$key]);
                    foreach($subKeyList as $key2)
                    {
                        echo "        subKey:".$key2."\n";
                    }
                }
            }
            */
            $arr['content']['match_data'] = [];
            $arr['content']['start_time'] = date("Y-m-d H:i:s",$arr['content']['start_time']);
            //unset($arr['content']['type'],$arr['content']['game']);

            foreach($keyList as $key)
            {
                if(!in_array($key,$currentKeyList))
                {
                    echo "key:".$key."\n";
                    $arr['content']['match_data'][$key] = $arr['content'][$key];
                    unset($arr['content'][$key]);
                }
            }
            foreach($arr['content']['round_list'] as $key => $round)
            {
                $roundInfo = ['tournament_id'=>$arr['content']['tournamentID'],'round_name'=>$round['name'],'round_id'=>$round['roundID']];
                $arr['content']['round_list'][$key] = $roundInfo;
            }
            $data['match_list'][] = getDataFromMapping($this->data_map['list'],$arr['content']);
        }
        return $data;
    }
}
