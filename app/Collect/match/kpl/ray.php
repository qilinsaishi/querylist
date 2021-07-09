<?php

namespace App\Collect\match\kpl;

class ray
{
    protected $data_map =
        [
            'team'=>[
                'game'=>['path'=>"",'default'=>"kpl"],//游戏
                'site_id'=>['path'=>"team_id",'default'=>0],//队伍ID
                'team_name'=>['path'=>"team_name",'default'=>0],//队伍名称
                'logo'=>['path'=>"team_logo",'default'=>''],//logo
                'original_source'=>['path'=>"",'default'=>'ray'],//初始来源
                'aka'=>['path'=>"",'default'=>[]],//别名
                'team_history'=>['path'=>"",'default'=>[]],//别名
            ],
            'list' => [
                'match_id' => ['path' => "id", 'default' => 0],//比赛唯一ID
                'match_status' => ['path' => "status", 'default' => 1],//比赛状态
                'game' => ['path' => "", 'default' => "kpl"],//游戏
                'home_score' => ['path' => "home_score", 'default' => 0],//主队得分
                'away_score' => ['path' => "away_score", 'default' => 0],//客队得分
                'home_id' => ['path' => "home_id", 'default' => 0],//主队id
                'away_id' => ['path' => "away_id", 'default' => 0],//客队id
                'home_logo' => ['path' => "home_logo", 'default' => ""],//主队logo
                'away_logo' => ['path' => "away_logo", 'default' => ""],//客队logo
                'home_name' => ['path' => "home_name", 'default' => ''],//主队名称
                'away_name' => ['path' => "away_name", 'default' => ''],//主队名称
                'home_odds' => ['path' => "home_odds", 'default' => ''],//主队赔率
                'away_odds' => ['path' => "away_odds", 'default' => ''],//主队客队
                "tournament_id" => ['path' => "tournament_id", 'default' => 0],//赛事唯一ID
                "tournament_name" => ['path' => "tournament_name", 'default' => 0],//赛事名称
                "tournament_logo" => ['path' => "tournament_logo", 'default' => 0],//赛事名称
                "start_time" => ['path' => "start_time", 'default' => ''],
                "end_time" => ['path' => "end_time", 'default' => ''],
                "game_bo" => ['path' => "round", 'default' => ''],
            ]
        ];
    public function collect($arr)
    {
        $cdata = $res= [];
        $res =$arr['detail'] ?? [];
        if(isset($res['round']) && strpos($res['round'],'bo') !==false){
            $res['round']=str_replace('bo','',$res['round']);
        }
        if(isset($res['tournament_logo']) && strpos($res['tournament_logo'],'statics.hnquant.com') ===false){
            $res['tournament_logo']='https://statics.hnquant.com'.$res['tournament_logo'];
        }
        //主队信息
        $res['home_id']=$res['team'][0]['team_id']??0;
        $res['home_name']=$res['team'][0]['team_name']??0;
        if(isset($res['team'][0]['team_logo']) && strpos($res['team'][0]['team_logo'],'statics.hnquant.com') ===false){
            $res['home_logo']='https://statics.hnquant.com'.$res['team'][0]['team_logo'];
        }
        if(isset($res['team'][0]['score']) && count($res['team'][0]['score'])>0){
            $res['home_score']=$res['team'][0]['score']['total']??0;
        }
        //客队信息
        $res['away_id']=$res['team'][1]['team_id']??0;
        $res['away_name']=$res['team'][1]['team_name']??0;
        if(isset($res['team'][1]['team_logo']) && strpos($res['team'][1]['team_logo'],'statics.hnquant.com') ===false){
            $res['away_logo']='https://statics.hnquant.com'.$res['team'][1]['team_logo'];
        }
        if(isset($res['team'][1]['score']) && count($res['team'][1]['score'])>0){
            $res['away_score']=$res['team'][1]['score']['total']??0;
        }
        $res['tournament_id']=$res['tournament_id'] ?? ($res['odds'][0]['tournament_id']??0);
        $res['home_odds']=$res['odds'][0]['odds']??0;
        $res['away_odds']=$res['odds'][1]['odds']??0;
        unset($res['odds']);
        unset($res['team']);
        if (count($res)>0) {
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
    {
        $data=[];
        $redis = app("redis.connection");
        if($arr['content']['type']=="match"){
            $arr['content']['start_time'] = isset($arr['content']['start_time']) ? $arr['content']['start_time'] : date("Y-m-d H:i:s", 0);
            $arr['content']['end_time'] = isset($arr['content']['end_time']) ? $arr['content']['end_time'] : date("Y-m-d H:i:s", 0);
            $arr['content']['home_logo'] = isset($arr['content']['home_logo']) ? getImage($arr['content']['home_logo'], $redis) : '';
            $arr['content']['away_logo'] = isset($arr['content']['away_logo']) ? getImage($arr['content']['away_logo'], $redis) : '';
            $arr['content']['tournament_logo'] = isset($arr['content']['tournament_logo']) ? getImage($arr['content']['tournament_logo'], $redis) : '';
            $teamData=[
                [
                    'team_id'=>$arr['content']['home_id'],
                    'team_name'=>$arr['content']['home_name'],
                    'team_logo'=>$arr['content']['home_logo'],
                ],
                [
                    'team_id'=>$arr['content']['away_id'],
                    'team_name'=>$arr['content']['away_name'],
                    'team_logo'=>$arr['content']['away_logo'],
                ]
            ];
            if(count($teamData)>0){
                foreach ($teamData as $key=>$teamInfo){
                    $data['team'][$key] = getDataFromMapping($this->data_map['team'],$teamInfo);
                }
            }
            $data['match_list'][] = getDataFromMapping($this->data_map['list'], $arr['content']);
        }
        return $data;

    }
}
