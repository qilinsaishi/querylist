<?php

namespace App\Collect\match\dota2;

use App\Models\MissionModel;
use QL\QueryList;

class wca
{
    protected $data_map =
        [
            'tournament'=>[
                'game'=>['path'=>"",'default'=>"dota2"],//游戏
                'tournament_id'=>['path'=>"tournament_id",'default'=>''],//赛事ID
                'tournament_name'=>['path'=>"tournament_name",'default'=>''],//赛事名称
                'start_time'=>['path'=>"start_time",'default'=>0],//开始时间
                'end_time'=>['path'=>"end_time",'default'=>0],//开始时间
                'logo'=>['path'=>"image_thumb",'default'=>''],//logo
                'pic'=>['path'=>"image_thumb",'default'=>''],//关联图片
                'game_logo'=>['path'=>"",'default'=>''],//关联游戏图片
            ],
            'list'=>[
                'match_id'=>['path'=>"match_id",'default'=>0],//比赛唯一ID
                'match_status'=>['path'=>"match_status",'default'=>0],//比赛状态
                'game'=>['path'=>"game",'default'=>"dota2"],//游戏
                'home_score'=>['path'=>"home_score",'default'=>0],//主队得分
                'away_score'=>['path'=>"away_score",'default'=>0],//客队得分
                'home_name'=>['path'=>"home_name",'default'=>''],//主队名称
                'away_name'=>['path'=>"away_name",'default'=>''],//主队名称
                'home_id'=>['path'=>"",'default'=>0],//主队id
                'away_id'=>['path'=>"",'default'=>0],//客队id
                'logo'=>['path'=>"",'default'=>""],//logo
                'home_logo'=>['path'=>"home_logo",'default'=>""],//主队logo
                'away_logo'=>['path'=>"away_logo",'default'=>""],//客队logo
                "game_bo"=>['path'=>"game_bo",'default'=>""],//轮次唯一ID
                "tournament_id"=>['path'=>"tournament_id",'default'=>""],//赛事唯一ID
                "start_time"=>['path'=>"start_time",'default'=>[]],
                "match_data"=>['path'=>"match_data",'default'=>[]],//赛事数据
                'tournament_name'=>['path'=>"tournament_name",'default'=>''],//赛事名称
            ]
        ];
    public function collect($arr)
    {
        $cdata = [];
        $url = $arr['detail']['url'] ??'';
        //获取比赛id
        $match_id=intval(str_replace(array('https://www.wca.com.cn/score/dota2/','/'),'',$url));
        if($match_id >0){
            $res=$arr['detail'] ?? [];
            $res['tournament_name']=$res['league_nam'] ??'';
            $res['match_id']=$match_id;
            unset($res['league_nam']);
            unset($res['url']);
            unset($res['logo']);
            unset($res['title']);
            unset($res['zhuangtai']);
            $match_data=$this->getWcaMitchDetailByUrl($url);//采集比赛详情数据
            $res['match_status']=$match_data['status'] ?? 0;
            $res['home_score']=$res['home_score'] ?? $match_data['home_score'];
            $res['away_score']=$res['away_score'] ?? $match_data['away_score'];
            unset($match_data['status']);
            unset($match_data['home_score']);
            unset($match_data['away_score']);
            unset($match_data['source']);
            $res['match_data']=$match_data;
            if(count($res)>0){
                $cdata = [
                    'mission_id' => $arr['mission_id'],
                    'content' => json_encode($res),
                    'game' => $arr['game'],
                    'source_link' =>$url,
                    'title' => $arr['title'] ?? '',
                    'mission_type' => $arr['mission_type'],
                    'source' => $arr['source'],
                    'status' => 1,
                ];
            }


        }else{
            $missionModel=new MissionModel();
            $missionModel->updateMission($arr['mission_id'],['mission_status'=>3]);
        }
        return $cdata;
    }
    public function process($arr)
    {
        $redis = app("redis.connection");
        //status:0表示未开赛，1已完赛

        $data = ['match_list'=>[]];

        $currentKeyList = array_column($this->data_map['list'],'path');

        $keyList = array_keys($arr['content']);
        $arr['content']['start_time'] = date("Y-m-d H:i:s",$arr['content']['start_time']);
        $arr['content']['home_logo']=getImage($arr['content']['home_logo'],$redis) ;
        $arr['content']['away_logo']=getImage($arr['content']['away_logo'],$redis) ;
        foreach($keyList as $key)
        {
            if(!in_array($key,$currentKeyList))
            {
                $arr['content']['match_data'][$key] = $arr['content'][$key];
                unset($arr['content'][$key]);
            }
        }
        $arr['content']['match_data'] = $this->processImg($arr['content']['match_data'],$redis);
        $data['match_list'][] = getDataFromMapping($this->data_map['list'],$arr['content']);

        return $data;
    }

    public function processImg($arr,$redis = null)
    {
        if(is_null($redis))
        {
            $redis = app("redis.connection");
        }
        foreach($arr as $key => $value)
        {
            if(is_array($value))
            {
                $arr[$key] = $this->processImg($value,$redis);
            }
            else
            {
                $arr[$key] = checkImg($value,$redis);
            }
        }
        return $arr;
    }
    public function getWcaMitchDetailByUrl($url){
        $qt=QueryList::get($url);
        $team_base_data=[];
        //=========================战队基础数据=============================
        $status_text=$qt->find('.score-panel .center .child .clearfix-row .clearfix-row')->text();
        $home_score=$qt->find('.score-panel .team:eq(0) span')->text();
        $away_score=$qt->find('.score-panel .team:eq(1) span')->text();

        if($status_text=='未开赛'){
            $status=0;
        }elseif($status_text=='完赛'){
            $status=1;
        }

        $blue_victory_rate=$qt->find('.team-data .team-data-content .basic-data .left .number p')->text();//蓝队胜率
        $blue_victory_rate=trim($blue_victory_rate,'%');//胜率
        $blue_victory_tip_text=$qt->find('.team-data .team-data-content .basic-data .left .tip-text')->text();//比赛场次
        $red_victory_rate=$qt->find('.team-data .team-data-content .basic-data .right .number p')->text();//红队胜率
        $red_victory_rate=trim($red_victory_rate,'%');//胜率
        $red_victory_tip_text=$qt->find('.team-data .team-data-content .basic-data .right .tip-text')->text();//比赛场次
        $blue_kda=$qt->find('.team-data .team-data-content .basic-data .middle .center-box .block')->text();
        $blue_kda_detail=$qt->find('.team-data .team-data-content .basic-data .middle .center-box .block2')->text();
        $red_kda=$qt->find('.team-data .team-data-content .basic-data .middle .center-box .block5')->text();
        //分解总击杀数，死亡数，助攻数
        $blue_kills=$blue_deaths=$blue_assists=$red_kills=$red_deaths=$red_assists=0;
        list($blue_kills,$blue_deaths,$blue_assists)=explode('/',$blue_kda_detail);

        $red_kda_detail=$qt->find('.team-data .team-data-content .basic-data .middle .center-box .block4')->text();
        list($red_kills,$red_deaths,$red_assists)=explode('/',$red_kda_detail);
        $data_list_item_list=[];
        $data_list_item=$qt->rules(array(
            'title' => array('.title','text'),
            'blue' => array('.row span:eq(0)','text'),
            'red' => array('.row span:eq(2)','text'),
        ))->range('.team-data .team-data-content .basic-data .middle  .basic-row')->queryData();
        if(count($data_list_item)>0){
            foreach ($data_list_item as $key=>$item){
                if($item['title']=='分均补刀'){
                    $data_list_item_list['minute_hits'] =$item;
                }
                if($item['title']=='分均经济'){
                    $data_list_item_list['average_money']=$item;
                }
                if($item['title']=='分均输出'){
                    $data_list_item_list['minute_output']=$item;
                }
                if($item['title']=='场均时长'){
                    $data_list_item_list['average_time']=$item;

                }
            }
        }

        //=========================战队统计=====================================
        $statistics=$qt->find('.team-data .team-data-content .statistics')->html();
        $bodyHtml=$qt->find('body')->html();
        $start = strpos($bodyHtml, " name: '2011年',");
        $end = strpos($bodyHtml, "var myChart = echarts.init(document.getElementById('echarts'));");
        $html3 = substr($bodyHtml, $start, $end - $start);


        $blue_start=strpos($html3,"data: [");
        $blue_end=strpos($html3,"label:");
        $blue_statistics_str=substr($html3, $blue_start, $blue_end - $blue_start);
        $blue_statistics_str=str_replace(array('data: [','],'),"",$blue_statistics_str);
        $blue_statistics_list=explode(',',$blue_statistics_str);

        $red_start=strripos($html3,"data: [");
        $red_end=strripos($html3,"label:");
        $red_statistics_str=substr($html3, $red_start, $red_end-$red_start);
        $red_statistics_str=str_replace(array('data: [','],'),"",$red_statistics_str);
        $red_statistics_list=explode(',',$red_statistics_str);
        $statistics_name_list=['一血率', '一塔率', '先十杀', '首肉山'];

        $statistics_list=['firstbloodkillrate'=>['name'=>'一血率'],'firsttowerrate'=>['name'=>'一塔率'], 'firsttenkill'=>['name'=>'先十杀'], 'shouroumountain'=>['name'=>'首肉山']];

        foreach ($statistics_name_list as $k=>$statisticsInfo){
            if($statisticsInfo=='一血率'){
                $statistics_list['firstbloodkillrate']['name']=$statisticsInfo;

                $statistics_list['firstbloodkillrate']['blue']=$blue_statistics_list[$k] ?? 0;

                $statistics_list['firstbloodkillrate']['red']=$red_statistics_list[$k] ?? 0;

            }
            if($statisticsInfo=='一塔率'){
                $statistics_list['firsttowerrate']['name']=$statisticsInfo;
                $statistics_list['firsttowerrate']['blue']=$blue_statistics_list[$k] ?? 0;
                $statistics_list['firsttowerrate']['red']=$red_statistics_list[$k] ?? 0;
            }
            if($statisticsInfo=='先十杀'){
                $statistics_list['firsttenkill']['name']=$statisticsInfo;
                $statistics_list['firsttenkill']['blue']=$blue_statistics_list[$k] ?? 0;
                $statistics_list['firsttenkill']['red']=$red_statistics_list[$k] ?? 0;
            }
            if($statisticsInfo=='首肉山'){
                $statistics_list['shouroumountain']['name']=$statisticsInfo;
                $statistics_list['shouroumountain']['blue']=$blue_statistics_list[$k] ?? 0;
                $statistics_list['shouroumountain']['red']=$red_statistics_list[$k] ?? 0;
            }

        }



        $team_base_data=[
            'blue_victory_rate'=>$blue_victory_rate,//蓝队胜率
            'blue_victory_tip_text'=>$blue_victory_tip_text,//蓝队比赛场次
            'blue_kda'=> $blue_kda,//蓝队kda
            'blue_kills'=>$blue_kills ??0,//蓝队击杀
            'blue_deaths'=>$blue_deaths ?? 0,//蓝队死亡
            'blue_assists'=>$blue_assists ?? 0,//蓝队助攻
            'red_victory_rate'=>$red_victory_rate,//红队胜率
            'red_victory_tip_text'=>$red_victory_tip_text,//红队比赛场次
            'red_kda'=> $red_kda,
            'red_kills'=>$red_kills ?? 0,
            'red_deaths'=>$red_deaths ?? 0,
            'red_assists'=>$red_assists ?? 0,
            'data_list_item'=>$data_list_item_list,
            'statistics_list'=>$statistics_list,

        ];
        //=========================战队基础数据=============================
        //========================队员基础数据==============================
        $player_item_data=[];
        $player_item_data=$qt->rules(array(
            'blue_playe_name' => array('.left .name','text'),
            'blue_playe_logo' => array('.left img','src'),
            'red_player_name' => array('.right .name','text'),
            'red_playe_logo' => array('.right img','src'),
            'player_item_infos' => array('.middle','html'),
            'player_item_foot' => array('.player-foot','html'),
        ))->range('.player-wrap .player-container .player-item')->queryData(function ($item){

            $item['player_item_infos'] = QueryList::html($item['player_item_infos'])->rules(array(
                'blue' => array('span:eq(0)','text'),
                'red' => array('span:eq(1)','text'),
                'title' => array('p','text'),
            ))->range('.row')->queryData();
            $player_item_list=[];
            if( count($item['player_item_infos'])>0){
                foreach ($item['player_item_infos'] as $player_info){
                    if($player_info['title']=='出场次数') {
                        $player_item_list['player_count']=$player_info;
                    }
                    if($player_info['title']=='K / D / A') {
                        $player_item_list['kda']=$player_info;
                    }
                    if($player_info['title']=='胜率') {
                        $player_info['blue']=trim($player_info['blue'],"%");
                        $player_info['red']=trim($player_info['red'],"%");
                        $player_item_list['win_rate']=$player_info;
                    }
                    if($player_info['title']=='参团率') {
                        $player_info['blue']=trim($player_info['blue'],"%");
                        $player_info['red']=trim($player_info['red'],"%");
                        $player_item_list['join_rate']=$player_info;
                    }
                }
            }
            $item['player_item_infos'] =$player_item_list;

            $item['blue_hero']=QueryList::html($item['player_item_foot'])->rules(array(
                'img' => array('img','src'),
                'count' => array('p','text'),
                'win_rate' => array('.chart-box .text','text'),
            ))->range('.hero:eq(0) .item')->queryData(function ($heroBlueItem){
                $heroBlueItem['count']=trim($heroBlueItem['count'],'局') ??0;
                $heroBlueItem['win_rate']=trim($heroBlueItem['win_rate'],'%') ??0;
                $heroBlueItem['lose_rate']=(1-$heroBlueItem['win_rate']/100)*100;
                return $heroBlueItem;
            });
            $item['red_hero']=QueryList::html($item['player_item_foot'])->rules(array(
                'img' => array('img','src'),
                'count' => array('p','text'),
                'win_rate' => array('.chart-box .text','text'),
            ))->range('.hero:eq(1) .item')->queryData(function ($heroRedItem){
                $heroRedItem['count']=trim($heroRedItem['count'],'局') ??0;
                $heroRedItem['win_rate']=trim($heroRedItem['win_rate'],'%') ??0;
                $heroRedItem['lose_rate']=(1-$heroRedItem['win_rate']/100)*100;
                return $heroRedItem;
            });
            $blue_start=strpos($item['player_item_foot'],'data-basic=');
            $blue_end=strpos($item['player_item_foot'],"data-basic2");
            $blue_indicator=substr($item['player_item_foot'],$blue_start,$blue_end-$blue_start);
            $blue_indicator=str_replace(array('data-basic="','" '),'',$blue_indicator);
            $blue_indicator_list=explode(',',$blue_indicator);

            $red_start=strpos($item['player_item_foot'],'data-basic2=');
            $red_indicator=substr($item['player_item_foot'],$red_start);
            $red_indicator=substr($red_indicator,strlen('data-basic2="'),strpos($red_indicator,'"></div>')-strlen('data-basic2="'));
            $red_indicator_list=explode(',',$red_indicator);
            $indicator_list=['economic'=>['name'=>'经济'],'injury'=>['name'=>'伤害'], 'hits'=>['name'=>'补刀'], 'assists'=>['name'=>'助攻'], 'output'=>['name'=>'输出'], 'kill'=>['name'=>'击杀']];
            foreach ($indicator_list as $indicatorKey=>$indicatorInfo){
                if($indicatorInfo['name']=='经济') {
                    $indicator_list[$indicatorKey]['blue']=$blue_indicator_list[0] ?? 0;
                    $indicator_list[$indicatorKey]['red']=$red_indicator_list[0] ?? 0;
                }
                if($indicatorInfo['name']=='伤害') {
                    $indicator_list[$indicatorKey]['blue']=$blue_indicator_list[1] ?? 0;
                    $indicator_list[$indicatorKey]['red']=$red_indicator_list[1] ?? 0;
                }
                if($indicatorInfo['name']=='补刀') {
                    $indicator_list[$indicatorKey]['blue']=$blue_indicator_list[2] ?? 0;
                    $indicator_list[$indicatorKey]['red']=$red_indicator_list[2] ?? 0;
                }
                if($indicatorInfo['name']=='助攻') {
                    $indicator_list[$indicatorKey]['blue']=$blue_indicator_list[3] ?? 0;
                    $indicator_list[$indicatorKey]['red']=$red_indicator_list[3] ?? 0;
                }
                if($indicatorInfo['name']=='输出') {
                    $indicator_list[$indicatorKey]['blue']=$blue_indicator_list[4] ?? 0;
                    $indicator_list[$indicatorKey]['red']=$red_indicator_list[4] ?? 0;
                }
                if($indicatorInfo['name']=='击杀') {
                    $indicator_list[$indicatorKey]['blue']=$blue_indicator_list[5] ?? 0;
                    $indicator_list[$indicatorKey]['red']=$red_indicator_list[5] ?? 0;
                }

            }
            $item['indicator_list']=$indicator_list ?? [];
            unset($item['player_item_foot']);
            return $item;
        });

        //========================队员基础数据==============================
        //========================ban数据==============================
        $ban_item_data=[];
        //主队ban
        $ban_home_data=$qt->rules(array(
            'hero_name' => array('.details .name','text'),
            'hero_img' => array('img','src'),
            'hero_win' => array('.details .txt','text'),

        ))->range('.ban-pick-wrap .ban  .list .home-box li')->queryData(function ($item){
            $item['hero_win']=trim($item['hero_win'],"%");
            return $item;
        });
        //客队ban
        $ban_away_data=$qt->rules(array(
            'hero_name' => array('.details .name','text'),
            'hero_img' => array('img','src'),
            'hero_win' => array('.details .txt','text'),

        ))->range('.ban-pick-wrap .ban  .list .away-box li')->queryData(function ($item){
            $item['hero_win']=trim($item['hero_win'],"%");
            return $item;
        });
        $ban_item_data=[
            'ban_home_data'=>$ban_home_data,
            'ban_away_data'=>$ban_away_data,
        ];

        //========================ban数据==============================
        //========================pick数据==============================
        $pick_item_data=[];
        //主队ban
        $pick_home_data=$qt->rules(array(
            'hero_name' => array('.details .name','text'),
            'hero_img' => array('img','src'),
            'hero_win' => array('.details .txt','text'),

        ))->range('.ban-pick-wrap .pick  .list .home-box li')->queryData(function ($item){
            $item['hero_win']=trim($item['hero_win'],"%");
            return $item;
        });
        //客队ban
        $pick_away_data=$qt->rules(array(
            'hero_name' => array('.details .name','text'),
            'hero_img' => array('img','src'),
            'hero_win' => array('.details .txt','text'),

        ))->range('.ban-pick-wrap .pick  .list .away-box li')->queryData(function ($item){
            $item['hero_win']=trim($item['hero_win'],"%");
            return $item;
        });
        $pick_item_data=[
            'pickn_home_data'=>$pick_home_data,
            'pick_away_data'=>$pick_away_data,
        ];

        //========================pick数据==============================
        //========================历史交锋数据===========================
        $history_confrontation_data=[];

        $blue_name=$qt->find('.history-wrap .top-box ')->attr('alt');
        $confrontation_data=$qt->rules(array(
            'blue_win_rate' => array('.block span:eq(0)','text'),
            'blue_win_count' => array('.block span:eq(1)','text'),
            'title' => array('.tt','text'),
            'red_win_rate' => array('.block2 span:eq(0)','text'),
            'red_win_count' => array('.block2 span:eq(1)','text'),

        ))->range('.history-wrap .top-box .his-list .row')->queryData(function ($item){
            $item['blue_win_rate']=trim($item['blue_win_rate'],"%胜率");
            $item['red_win_rate']=trim($item['red_win_rate'],"%胜率");
            $item['blue_win_count']=trim($item['blue_win_count'],"胜");
            $item['red_win_count']=trim($item['red_win_count'],"胜");
            return $item;
        });
        $history_list=$qt->rules(array(
            'name' => array('.name','text'),
            'time' => array('.time','text'),
        ))->range('.history-wrap .list-con  .item')->queryData();
        $history_red_list=$qt->rules(array(
            'name' => array('.name','text'),
            'time' => array('.time','text'),
        ))->range('.history-wrap .list-con  .win')->queryData();

        foreach ($history_list as $historyKey=>$historyInfo){
            if(!in_array($historyInfo['time'],array_column($history_red_list,'time'))) {
                $history_list[$historyKey]['color']='blue';
            }else{
                $history_list[$historyKey]['color']='red';
            }

        }
        $history_confrontation_data=[
            'history_list'=>$history_list,
            'confrontation_base_data'=>$confrontation_data
        ];

        //========================历史交锋数据===========================
        //========================最近战绩数据===========================
        $recentMatchList=[];

        $recentBlueMatchList=$qt->rules(array(
            'match_result' => array('.item:eq(0)','text'),
            'score' => array('.item:eq(1)','text'),
            'opponent_name' => array('.item:eq(2) .name','text'),
            'opponent_logo' => array('.item:eq(2) img','src'),
            'time' => array('.item:eq(3)','text'),

        ))->range('.recent-box .bolck  li:gt(0)')->queryData();

        $recentRedMatchList=$qt->rules(array(
            'match_result' => array('.item:eq(0)','text'),
            'score' => array('.item:eq(1)','text'),
            'opponent_name' => array('.item:eq(2) .name','text'),
            'opponent_logo' => array('.item:eq(2) img','src'),
            'time' => array('.item:eq(3)','text'),

        ))->range('.recent-box .bolck2  li:gt(0)')->queryData();
        //最近比赛
        $recentMatchList=[
            'recentBlueMatchList'=>$recentBlueMatchList,//蓝队最近比赛
            'recentRedMatchList'=>$recentRedMatchList,//红队最近比赛
        ];

        //========================最近战绩数据===========================


        $data=[
            'team_base_data'=>$team_base_data,
            'player_base_data'=>$player_item_data,
            'ban_item_data'=>$ban_item_data,
            'pick_item_data'=>$pick_item_data,
            'history_confrontation_data'=>$history_confrontation_data,//历史交锋
            'recentMatchList'=>$recentMatchList,//最近比赛
            'status'=>$status ??0,
            'home_score'=>(isset($home_score) && $home_score !='') ? $home_score:0,
            'away_score'=>(isset($away_score) && $away_score!='') ? $away_score:0,
        ];
        return $data;
    }
}
