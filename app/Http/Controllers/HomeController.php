<?php

namespace App\Http\Controllers;

use App\Libs\AjaxRequest;
use App\Libs\ClientServices;
use App\Models\Admin\DefaultConfig;
use App\Models\CollectResultModel;
use App\Models\TeamModel;
use QL\QueryList;

class HomeController extends Controller
{
    public function lists(){
        $id=$this->request->post('id','');echo $id.'<br/>';
        $name=$this->request->input('name','');echo $name.'<br/>';
        $data=$this->payload;

        $all=$this->request->all();

    }

    public function teamInfo(){

        //$team_id=$this->request->input('team_id','');
        $teamModel=new TeamModel();
        $params=[];
        $params['page_size']=1000;
        $params['game']='lol';
        $teamList=$teamModel->getTeamList($params);
        $data=[];
        $arr=[];
        $cdata=[];
        if(!empty($teamList)){
            foreach ($teamList as $key=>$val){
                if(!empty($val['team_name'])){
                    if(isset($data[$val['team_name']])){
                        {
                            $arr[$val['team_name']] = ($arr[$val['team_name']]??1)+1;
                        }
                    }
                    $data[$val['team_name']] = $val['team_id'];

                }

                if(!empty($val['en_name'])){
                    if(isset($val['en_name'])){
                        if(isset($data[$val['en_name']]))
                        {
                            $arr[$val['en_name']] = ($arr[$val['en_name']]??1)+1;
                        }
                    }
                    $data[$val['en_name']]=$val['team_id'];
                }

                //$data[$key]['team_id']=$val['team_id'] ?? '';
                $aka=json_decode($val['aka'],true);
                if(!empty($aka) && is_array($aka)){
                    foreach ($aka as $k=>$v){
                        if(isset($v)){
                            if(isset($data[$v]))
                            {
                                $arr[$v] = ($arr[$v]??1)+1;
                            }

                        }
                        $data[$v]=$val['team_id'];
                    }
                }

            }
        }
        ksort($data);
        $cdata=[
            'arr_count'=>count($arr),
            'datacount'=>count($data),
            'arr'=>$arr ?? [],

            'data'=> $data ?? []
        ];
        print_r($cdata);exit;

    }
    public function getLevelData(){
        $levelData=[];
        $neutralitems='https://www.dota2.com.cn/neutralitems/json';
        $itemData=curl_get($neutralitems);
        foreach ($itemData as $k=>$v){
            foreach ($v as $v1){
                $levelData[$v1]=str_replace('level_','',$k);
            }

        }
        return $levelData;
    }
    //dota2官网赛事
    public function getGmaeDotaMatch($url,$type){
        $data=[];
        $dpcList=curl_get($url);
        if($dpcList['status']=='success'){
            $return=$dpcList['result'] ?? [];
            $current_season=$return['current_season'] ?? 0;//当前赛季
            $selected_phase=$return['selected_phase'] ?? '';//所有阶段
            $data=$return['data'] ?? [];
            if(count($data) > 0){
                foreach ($data as $k=>&$v){
                    $v['type']=$type;
                    $v['season']=$current_season;

                }
            }

        }
        return $data;
    }
    //dota2赛事赛程列表
    public function dotaMatchList(){
        //赛事赛程列表
        $url='https://www.wca.com.cn/e/action/score.php';
        $curtime=date('Y-m-d',strtotime('sunday'));echo $curtime;
        $timestamp = time();
        $param = [
            'time' => '2021-05-30',
            'id' => '2',
            'page' => '0',
            'action'=>'score'
        ];
        $headers=['origin'=>'https://www.wca.com.cn'];

        $client=new ClientServices();
        $data=$client->curlPost($url,$param,$headers);print_r($data);exit;
        $data=curl_post($url,$param);
        return $data;
    }


    public function index()
    {
        echo getImage('http://www.2cpseo.com/img-asset/small/storage/images/29936c1a12e25c643da1c8100d201ec0.png');exit;
        $url='https://www.wca.com.cn/score/dota2/6536/';
        $qt=QueryList::get($url);
        $team_base_data=[];
        //=========================战队基础数据=============================
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
        $red_kda_detail=$qt->find('.team-data .team-data-content .basic-data .middle .center-box .block4')->text();
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
        $blue_name=$qt->find('.history-wrap .top-box ')->attr('alt');print_r($blue_name);exit;
        $confrontation_data=$qt->rules(array(
            'hero_name' => array('.details .name','text'),
            'hero_img' => array('img','src'),
            'hero_win' => array('.details .txt','text'),

        ))->range('.history-wrap .top-box .row')->queryData(function ($item){
            $item['hero_win']=trim($item['hero_win'],"%");
            return $item;
        });

        //========================历史交锋数据===========================



        $data=[
            'team_base_data'=>$team_base_data,
            'player_base_data'=>$player_item_data,
            'ban_item_data'=>$ban_item_data,
            'pick_item_data'=>$pick_item_data,
        ];
        print_r($data);exit;



//$statistics_list['name']
        print_r($team_base_data);exit;






        $bilibiList=curl_get('https://www.dota2.com.cn/international/2019/rank?task=main_map');//接口链接
        print_r($bilibiList);exit;
        $url='http://qilingsaishi-01.oss-cn-hangzhou.aliyuncs.com/storage/downloads/0888a90f7b73065e7ace05b692727b52.png';
        //判断url是否有效
        $headers=get_headers($url,1);
        if(!preg_match('/200/',$headers[0])){
            return  [];
        }
        /*$url='https://www.scoregg.com/big-data/team/5?tournamentID=&type=baike';
        $qt=QueryList::get($url);
        $cn_name=$qt->find('.right-content .intro h2')->text();
        $content=$qt->find('.right-content .baike-content')->html();
        //荣誉历程
        $history_honor=$qt->rules(array(
            'match_time' => array('.td-item:eq(0)','text'),//时间
            'ranking' => array('.td-item:eq(1)','text'),//荣誉/名词
            'ranking_icon' => array('.td-item:eq(1) img','src'),//荣誉/名词 rank-icon
            't_image' => array('.td-item:eq(2) img','src'),//赛事图片
            't_name' => array('.td-item:eq(2) ','text'),//赛事名称
            'team_a_image' => array('.td-item:eq(3) .team-a img','src'),//赛事图片
            'team_name_a' => array('.td-item:eq(3)  .team-a ','html'),//赛事名称
            'team_win' => array('.td-item:eq(3)  .vs-score ','text'),//赛事名称
            'team_b_image' => array('.td-item:eq(3)  .team-b img','src'),//赛事图片
            'team_name_b' => array('.td-item:eq(3)   .team-b ','html'),//赛事名称
            'bonus' => array('.td-item:eq(4) ','text')//奖金
    ))->range('.history-honor .article-list .article-table  .border-bottom')->queryData(function ($item){
            $item['ranking']=trim($item['ranking']);
            $tempNames_a=explode('alt="',$item['team_name_a']);
            $tempNames_a=explode('" class="team-logo"',$tempNames_a[1]);
            $item['team_name_a']=$tempNames_a[0] ?? '';
            $tempWins=explode(' : ',$item['team_win']);
            $item['team_a_win']=$tempWins[0] ?? 0;
            $item['team_b_win']=$tempWins[1] ?? 0;
            $tempNames_b=explode('alt="',$item['team_name_b']);
            $tempNames_b=explode('" class="team-logo"',$tempNames_b[1]);
            $item['team_name_b']=$tempNames_b[0] ?? '';
            unset($item['team_win']);

        return $item;
    });
        //现役队员
        $play_list=$qt->rules(array(
            'nickname' => array('td:eq(0) .name','text'),//队员昵称
            'player_url' => array('td:eq(0)  a','href'),//队员id
            'player_image' => array('td:eq(0) img','src'),//队员图片
            'position_name' => array('td:eq(0) .id','text'),//位置
            'join_time' => array('td:eq(2) ','text'),//加入时间
            'contract_end_time' => array('td:eq(3)','text'),//合同到期时间
        ))->range('.left-content .article-table .tr-item')->queryData(function ($item){
            $playerID=str_replace('/big-data/player/','',$item['player_url']);
            $item['player_url']='https://www.scoregg.com'.$item['player_url'];
            $item['playerID']=$playerID;
            return $item;
        });
        $bainfo=[
            'cn_name'=>$cn_name,
            'team_history'=>$content,
            'play_list'=>$play_list,
            'history_honor'=>$history_honor,
        ];print_r($bainfo);exit;*/

        //$en_name=$qt->find('.left-content .card-info .player-more-info .text-label')->text();
        $url='https://www.scoregg.com/services/api_url.php';
        //$limit=12;
        $gameId=1;
        $tournament_id=191;
        $param = [
            'api_path' => '/services/gamingDatabase/match_data_ssdb_list.php',
            'method' => 'post',
            'platform' => 'web',
            'api_version' => '9.9.9',
            'language_id' => 1,
            'tournament_id' => '',
            'type' => 'player',
            'order_type' => 'KDA',
            'order_value' => 'DESC',
            'team_name' => '',
            'player_name' => '',
            'positionID' => '',
            'page' => 1,

        ];
        $data=curl_post($url,$param);
        $totalCount=$data['data']['data']['count'] ?? 0;
        $pageCount=ceil($totalCount/12);
        if($totalCount !=0){
            $totalPage=ceil($totalCount/12);
            for ($i=1;$i<=$totalPage;$i++){
                $param['page']=$i;
                $cdata=curl_post($url,$param);
                $list[$i]=$cdata['data']['data']['list'] ?? 0;
                if(count($list[$i])>0){
                    foreach ($list[$i] as $k=>&$val){
                        $ajax_url='https://www.scoregg.com/big-data/player/'.$val['player_id'].'?tournamentID='.$tournament_id.'&type=baike';
                        $val['player_url']=$ajax_url;
                    }
                }

            }
        }
        print_r($list);exit;

        print_r($data);exit;



        //////////////////////
        $url='https://www.wanplus.com/kog/event';
        $items=QueryList::get($url)->rules(array(
            'title' => array('.event-title','text'),//标题
            'status' => array('.event-pag','text'),//描述
            'logo' => array('img','src'),//图片
            'link' => array('a ','href'),//链接
            'dates' => array('p:eq(1)','text')//链接
        ))->range('.event-list .ov  li')->queryData(function ($item){
            $item['link']='https://www.wanplus.com'.$item['link'];
            if($item['status']=='进行中'){
                $item['status']=1;
            }elseif($item['status']=='未开始'){
                $item['status']=2;
            }elseif($item['status']=='已结束'){
                $item['status']=3;
            }else{
                $item['status']=0;
            }
            $dates=explode(' — ',$item['dates']);
            $item['start_time']=$dates[0] ?? 0;
            $item['end_time']=$dates[1] ?? 0;
            unset($item['dates']);

            //$item['status']='https://www.wanplus.com'.$item['link'];
            return $item;
        });print_r($items);exit;

        /*$url='https://www.wanplus.com/lol/video/1563559';
        //$url='https://www.wanplus.com/dota2/video/260935';
        $qt=QueryList::get($url);
        $content=$qt->find('.content .ov #video-video')->html();
        print_r($content);exit;*/
        $AjaxModel = new AjaxRequest();
        $totalpages=62;
        $gametype=2;
        $game='lol';
        $clist=[];
        for($i=1;$i<=$totalpages;$i++){

            $url='https://www.wanplus.com/ajax/video/getlist?gametype='.$gametype.'&page='.$i.'&totalpages=62&type=video&subject=&subSubject=&sort=new';
            $cdata=$AjaxModel->ajaxGetData($url);
            $cdata=$cdata['list'] ?? [];
            if(count($cdata) > 0){
                foreach ($cdata as $k=>$val){//echo date("Y-m-d H:i:s",$val['created'])."\n";

                    $detail=[
                        'url'=>'https://www.wanplus.com/dota2/video/'.$val['id'],
                        'title'=>$val['title'],
                        'author'=>$val['anchor'],
                        'create_time'=>$val['released'],
                        'duration'=>$val['duration'],//时长
                        'logo'=>$val['img'],
                        'remark'=>$val['title'],
                        'game'=>$game,
                        'site_id'=>$val['id'],
                        'source'=>'wanplus',
                        'gametype'=>$gametype,
                        'type'=>'video'
                    ];

                }
            }

        }exit;exit;
        //$AjaxModel->ajaxGetData($url, $param = [])


        $data=[];
        $count=5;
        for($i=1;$i<=$count;$i++){
            $url='https://www.dota2.com.cn/Activity/gamematch/index'.$i.'.htm';
            $item=QueryList::get($url)->rules(array(
                'title' => array('.brief h3','text'),//标题
                'desc' => array('.brief p','text'),//描述
                'logo' => array('img','src'),//图片
                'link' => array('a ','href')//链接
            ))->range('.content .activities  .activity')->queryData();
            if(count($item) >0){
                foreach ($item as $key=>$val){
                    if(strpos($val['link'],'pwesports.cn')!==false) {
                        $type=str_replace(array('https://','.pwesports.cn/'),'',$val['link']);
                        $detail_url='https://esports.wanmei.com/'.$type.'-match/latest';
                        $val['detail_url']=$detail_url;print_r($val);
                        $data[$key]=$val;
                        echo $type."\n";
                    }
                }
            }

        }



        exit;



        // $qt=QueryList::get('https://www.dota2.com.cn/items/index.htm');
        $item=QueryList::get('https://www.dota2.com.cn/items/index.htm')->rules(array(
            'typename' => array('h4','text'),//类型名称
            'type' => array('img','src'),//类型名称
            'typeList' => array('.floatItemImage ','htmls')//介绍
        ))->range('#itemPickerInner .shopColumn')->queryData(function($item){
            $item['type']=str_replace(array('./images/itemcat_','.png'),'',$item['type']);
            foreach($item['typeList'] as &$val) {
                $img=QueryList::html($val)->find('img')->attr('src');
                $img=str_replace(array('./images/','_lg.png'),'',$img);
                $val=$img;
            }
            return $item;
        });
        $typeList=[];
        foreach ($item as $val){
            foreach ($val['typeList'] as $v){
                $typeList[$v]=[
                    'type'=>$val['type'],
                    'typename'=>$val['typename']

                ];
            }

        }
        print_r(count($typeList));exit;
        //物品
        $item_url='https://www.dota2.com.cn/items/json';
        $itemData=curl_get($item_url);
        if($itemData){
            foreach ( $itemData['itemdata'] as $key=>$val) {
                $val['en_name']=$key;
            }
        }
        print_r(count($itemData['itemdata']));exit;

        //dota2英雄
        $qt=QueryList::get('https://www.dota2.com.cn/hero/anti_mage/');
        $logo_small=$qt->find(".id_div .top_hero_card img")->attr('src');
        $logo_small='https://www.dota2.com.cn'.$logo_small;
        $herotitle=$qt->find(".id_div .top_hero_card p")->text();
        $logo_big=$qt->find(".item_left .hero_info .hero_b")->attr('src');
        $logo_big='https://www.dota2.com.cn'.$logo_big;
        $logo_icon=$qt->find(".item_left .hero_info .hero_name img")->attr('src');
        $logo_icon='https://www.dota2.com.cn'.$logo_icon;
        $hero_name=$qt->find(".item_left .hero_info .hero_name ")->text();
        $hero_en_name=str_replace($hero_name,'',$herotitle);
        //攻击类型
        $atk=$qt->find(".item_left .hero_info .info_ul li:eq(0) .info_p")->text();
        //定位
        $roles=$qt->find(".item_left .hero_info .info_ul li:eq(1) .info_p")->text();
        $roles=rtrim($roles, "-");
        $roles=explode('-',$roles);
        if(count($roles)>0){
            foreach ($roles as &$val){
                $val=trim($val);
            }
        }
        $roles=$roles;
        //阵营
        $radiant=$qt->find(".item_left .hero_info .info_ul li:eq(2) .info_p")->text();//阵营名称
        $radiant_logo=$qt->find(".item_left .hero_info .info_ul li:eq(2) .info_p img")->attr('src');
        if(strpos($radiant_logo,'https') ===false){
            $radiant_logo='https:'.$radiant_logo;
        }
        //其他简称
        $other_name=$qt->find(".item_left .hero_info .info_ul li:eq(3) .info_p")->text();//阵营名称
        $other_name=explode('、',$other_name);
        $aka=$other_name;
        //英雄属性
        $pro_box=[
            [
                'property_img'=>'https://www.dota2.com.cn/images/heropedia/overviewicon_str.png',
                'property_title'=>$qt->find(".item_left .property_box .pro6_box li:eq(0) .pop_property_t")->text(),
                'property_cont'=>$qt->find(".item_left .property_box .pro6_box li:eq(0) .pop_property_cont")->html(),
            ],
            [
                'property_img'=>'https://www.dota2.com.cn/images/heropedia/overviewicon_agi.png',
                'property_title'=>$qt->find(".item_left .property_box .pro6_box li:eq(1) .pop_property_t")->text(),
                'property_cont'=>$qt->find(".item_left .property_box .pro6_box li:eq(1) .pop_property_cont")->html(),
            ],
            [
                'property_img'=>'https://www.dota2.com.cn/images/heropedia/overviewicon_int.png',
                'property_title'=>$qt->find(".item_left .property_box .pro6_box li:eq(2) .pop_property_t")->text(),
                'property_cont'=>$qt->find(".item_left .property_box .pro6_box li:eq(2) .pop_property_cont")->html(),
            ],
            [
                'property_img'=>'https://www.dota2.com.cn/event/201401/herodata/images/pro4.png',
                'property_title'=>$qt->find(".item_left .property_box .pro6_box li:eq(3) .pop_property_t")->text(),
                'property_cont'=>$qt->find(".item_left .property_box .pro6_box li:eq(3) .pop_property_cont")->html(),
            ],
            [
                'property_img'=>'https://www.dota2.com.cn/event/201401/herodata/images/pro5.png',
                'property_title'=>$qt->find(".item_left .property_box .pro6_box li:eq(4) .pop_property_t")->text(),
                'property_cont'=>$qt->find(".item_left .property_box .pro6_box li:eq(4) .pop_property_cont")->html(),
            ],
            [
                'property_img'=>'https://www.dota2.com.cn/event/201401/herodata/images/pro6.png',
                'property_title'=>'',
                'property_cont'=>'',
            ],
        ];
        if($pro_box){
            foreach ($pro_box as &$val){
                if($val['property_cont']){
                    $val['property_cont']=explode('<br>',$val['property_cont']);
                    foreach ($val['property_cont'] as &$v){
                        $v=trim($v);
                    }
                }else{
                    $val['property_cont']=[];
                }

            }
        }
        //背景故事
        $story_box=$qt->find(".item_right .story_box")->text();
        $story_pic=$qt->find(".item_right .story_box .story_pic img")->attr('src');
        $story_pic='https://www.dota2.com.cn'.$story_pic;
        //天赋树
        $talent_box_html=$qt->find('.item_right  .talent_box')->html();
        $talent_box=QueryList::html($talent_box_html)->rules(array(
            'level' => array('.level-interior','text'),//等级
            'explain' => array('.talent-explain','texts')//介绍
        ))->range('.talent_ul li')->queryData();

        //技能
        $skill_box_html=$qt->find('.item_right  .skill_box')->html();
        $skill_box= QueryList::html($skill_box_html)->rules(array(
            'skill_img' => array('.skill_wrap img','src'),
            'title' => array('.skill_wrap .skill_intro span','text'),//标题
            'skill_intro' => array('.skill_wrap .skill_intro','text'),//技能描述
            'icon_xh' => array('.skill_wrap .xiaohao_wrap .icon_xh','text'),//魔法消耗
            'icon_lq' => array('.skill_wrap .xiaohao_wrap .icon_lq','text'),//冷却时间
            'skill_bot' => array(' .skill_bot','text'),
            'skill_list' => array('.skill_ul','html')
        ))->range('#focus_dl dd')->queryData(function($item){
            $item['skill_img']='https://www.dota2.com.cn'.$item['skill_img'];
            $item['skill_intro']=trim(str_replace($item['title'],'',$item['skill_intro']));
            $skill_ul=QueryList::html($item['skill_list'])->find('li')->texts()->all();//技能属性
            $item['skill_list'] = $skill_ul;
            return $item;
        });
        //装备选择
        $equip_wrap=$qt->find(".item_right .equip_wrap")->html();
        $equip_box= QueryList::html($equip_wrap)->rules(array(
            'equip_type' => array('.equip_t','text'),
            'equip_info' => array('.equip_ul','html'),//x
        ))->range('.equip_one')->queryData(function($item){
            $item['equip_info'] = QueryList::html($item['equip_info'])->rules(array(
                'equip_imgs' => array('img','src'),//装备缩略图
                'equip_title' => array('.pop_box .equip_item_r span','text'),//标题
                'equip_money' => array('.pop_box .equip_item_r  .equip_money','text'),//价格
                'use'=>array('.pop_box h1','texts'),//使用说明
                'pop_skill_p'=>array('.pop_box .pop_skill_p','texts'),//属性
                'pop_skill_s'=>array('.pop_box .pop_skill_s','text'),//描述
            ))->range('li')->queryData(function($item1){
                unset($item1['pop_skill_p'][0]);
                if(strpos($item1['equip_imgs'],'https')===false){
                    $item1['equip_imgs']='https:'.$item1['equip_imgs'];
                }

                return $item1;
            });
            return $item;
        });

        $heroInfo=[
            'hero_name'=>$hero_name,//英雄名称
            'hero_cn_name'=>$hero_name,//中文名称
            'hero_en_name'=>$hero_en_name,//英文名称
            'aka'=>$aka,//其他简称
            'logo_small'=>$logo_small,//小图片
            'logo_big'=>$logo_big,//大图片
            'logo_icon'=>$logo_icon,//icon
            'atk'=>$atk,//攻击类型
            'roles'=>$roles,//定位
            'radiant'=>$radiant,//阵营名称
            'radiant_logo'=>$radiant_logo,//别名，其他简称
            'story_box'=>$story_box,//背景故事
            'story_pic'=>$story_pic,//背景故事图片
            'talent_box'=>$talent_box,//天赋树
            'pro_box'=>$pro_box,//英雄属性
            'skill_box'=>$skill_box,//技能介绍
            'equip_box'=>$equip_box,//装备选择
        ];
        print_r($heroInfo);exit;
        //




        //==========================================
        $item=[];
        $typeItem=[];
        //力量
        $item0=$qt->find(".black_cont .goods_main .hero_list:eq(0) li a")->attrs('href');
        $item3=$qt->find(".black_cont .goods_main .hero_list:eq(3) li a")->attrs('href');
        if(count($item0) >0){
            foreach ($item0 as $k=>$v){
                array_push($item,$v);
                array_push($typeItem,'str');
            }
        }
        if(count($item3) >0){
            foreach ($item3 as $k=>$v){
                array_push($item,$v);
                array_push($typeItem,'str');
            }
        }


        //敏捷
        $item1=$qt->find(".black_cont .goods_main .hero_list:eq(1) li a")->attrs('href');
        $item4=$qt->find(".black_cont .goods_main .hero_list:eq(4) li a")->attrs('href');
        if(count($item1) >0){
            foreach ($item1 as $k=>$v){
                array_push($item,$v);
                array_push($typeItem,'agi');
            }
        }
        if(count($item4) >0){
            foreach ($item4 as $k=>$v){
                array_push($item,$v);
                array_push($typeItem,'agi');
            }
        }

        //智力
        $item2=$qt->find(".black_cont .goods_main .hero_list:eq(2) li a")->attrs('href');
        $item5=$qt->find(".black_cont .goods_main .hero_list:eq(5) li a")->attrs('href');
        if(count($item2) >0){
            foreach ($item2 as $k=>$v){
                array_push($item,$v);
                array_push($typeItem,'int');
            }
        }
        if(count($item5) >0){
            foreach ($item5 as $k=>$v){
                array_push($item,$v);
                array_push($typeItem,'int');
            }
        }
        print_r($item);print_r($typeItem);exit;
        /*  $data = QueryList::get('https://www.dota2.com.cn/heroes/index.htm')->rules([
              'title' => ['.news_msg .title', 'text'],
              'remark' => ['.news_msg .content', 'text'],
              'create_time' => ['.news_msg .date', 'text'],
              'logo' => ['.news_logo img', 'src']
          ])->range('#news_lists .panes .active a')
              ->queryData();*/







        //比赛列表
        //获取每周的周一时间;
        /*$AjaxModel = new AjaxRequest();
        $weekday=date("w");
        $weekday=($weekday + 6) % 7;
        $date=strtotime(date('Y-m-d',strtotime("-{$weekday} day")));
        $url='http://www.wanplus.com/ajax/schedule/list';
        $param=[
            'game'=>2,
            'time'=>$date,
            'eids'=>''
        ];
        $list=$AjaxModel->getMatchList($url, $param );
        if(isset($list['scheduleList'])){
            foreach($list['scheduleList'] as $val) {
                //https://www.wanplus.com/schedule/68605.html
                if(isset($val['list'])){
                    foreach ($val['list'] as $v){
                        $url='https://www.wanplus.com/schedule/'.$v['scheduleid'].'.html';
                        echo $url;
                        print_r($v);exit;
                    }
                }
            }
        }*/
        /*$schedule_url='http://www.wanplus.com/lol/schedule';
        $ql = QueryList::get($schedule_url);
        $data_eid=$ql->find('.slide-list li')->attrs('data-eid')->all();
        $data_gametype=$ql->find('.slide-list li')->attrs('data-gametype')->all();
        $data_texts=$ql->find('.slide-list li')->texts()->all();
print_r( $data_eid);exit;*/
        //$list_url='http://www.wanplus.com/ajax/schedule/list';
        //print_r($slide_list);exit;

        //比赛详情
        $url='https://www.wanplus.com/schedule/68605.html';
        $ql = QueryList::get($url);
        $event_title=$ql->find('.box h1')->text();
        $event_url=$ql->find('.box h1 a')->attr('href');
        $event_url='http://www.wanplus.com'.$event_url;
        $game_matchid=$ql->find('.box .game a')->attr('data-matchid');
        $game_matchname=$ql->find('.box .game a')->text();//
        $data=[];

        $matchInfo=[
            'event_title'=>$event_title,
            'event_url'=>$event_url,
            'match_id'=>$game_matchid,
            'match_name'=>$game_matchname,
        ];
        $data['matchInfo']=$matchInfo;

        $score=$ql->find('.box .team-detail li:eq(1) p')->text();
        $matchInfo['status']=$ql->find('.box .team-detail li:eq(1) .end')->text();
        $matchInfo['time']=$ql->find('.box .team-detail li:eq(1) .time')->text();


        $url='http://www.wanplus.com/ajax/matchdetail/'.$game_matchid;
        $playData= $AjaxModel->getHistoryMatch($url);
        //期间
        $matchInfo['match_duration']=$playData['info']['duration'];
        //战队信息
        $playData['info']['oneteam']['team_img']="https://static.wanplus.com/data/lol/team/".$playData['info']['oneteam']['teamid']."_mid.png";
        $playData['info']['twoteam']['team_img']="https://static.wanplus.com/data/lol/team/".$playData['info']['twoteam']['teamid']."_mid.png";
        $matchInfo['teamInfo'][0]=$playData['info']['oneteam'];
        $matchInfo['teamInfo'][1]=$playData['info']['twoteam'];
        if(strpos($score,':') !==false){
            $scores=explode(':',$score);
        }
        $matchInfo['teamInfo'][0]['score']=$scores[0] ?? 0;
        $matchInfo['teamInfo'][1]['score']=$scores[1] ?? 0;
        //击杀数
        $matchInfo['teamStatsList']['kills']=$playData['teamStatsList']['kills'] ?? [];
        //金钱数
        $matchInfo['teamStatsList']['golds']=$playData['teamStatsList']['golds'] ?? [];
        //推塔数
        $matchInfo['teamStatsList']['towerkills']=$playData['teamStatsList']['towerkills'] ?? [];
        //小龙数
        $matchInfo['teamStatsList']['dragonkills']=$playData['teamStatsList']['dragonkills'] ?? [];
        //大龙数
        $matchInfo['teamStatsList']['baronkills']=$playData['teamStatsList']['baronkills'] ?? [];
        //战队关联英雄
        if(isset($playData['bpList']['bans']) && $playData['bpList']['bans']){
            foreach ($playData['bpList']['bans'] as $key=>&$val){
                if($val){
                    foreach ($val as $k=>&$v){
                        $v['img_url']='https://static.wanplus.com/data/lol/hero/square/'.$v['cpherokey'].'.'.$playData['info']['heroImgSuffix'];
                        $matchInfo['teamHero'][$key][$k]['hero_img']=$v['img_url'];
                        $matchInfo['teamHero'][$key][$k]['teamid']=$v['teamid'];
                        $matchInfo['teamHero'][$key][$k]['en_name']=$v['cpherokey'];
                    }
                }
            }
        }
        //队员
        if(isset($playData['plList']) && $playData['plList']){
            foreach ($playData['plList'] as $key=>$val){
                if($val){
                    foreach ($val as $k=>$v){//print_r($v);exit;
                        $matchInfo['playInfo'][$key][$k]['player_img']="https://static.wanplus.com/data/lol/player/".$v['playerid']."_mid.png";
                        $matchInfo['playInfo'][$key][$k]['playername']=$v['playername'];
                        //kda
                        $matchInfo['playInfo'][$key][$k]['kills']=$v['kills'];//杀死
                        $matchInfo['playInfo'][$key][$k]['deaths']=$v['deaths'];//死亡
                        $matchInfo['playInfo'][$key][$k]['assists']=$v['assists'];//助攻
                        $matchInfo['playInfo'][$key][$k]['kda']=$v['kda'];
                        //金钱
                        $matchInfo['playInfo'][$key][$k]['gold']=$v['gold'];
                        //补刀
                        $matchInfo['playInfo'][$key][$k]['lasthit']=$v['lasthit'];
                        //输出伤害
                        $matchInfo['playInfo'][$key][$k]['totalDamageDealtToChampions']=$v['stats']['totalDamageDealtToChampions'];
                        //承受伤害
                        $matchInfo['playInfo'][$key][$k]['totalDamageTaken']=$v['stats']['totalDamageTaken'];
                        //英雄图片
                        $matchInfo['playInfo'][$key][$k]['heroImg']="https://static.wanplus.com/data/lol/hero/square/".$v['cpherokey'].'.'.$playData['info']['heroImgSuffix'];
                        $skill="https://static.wanplus.com/data/lol/skill/".$v['skill1id'].".png";
                        $skill2="https://static.wanplus.com/data/lol/skill/".$v['skill2id'].".png";
                        //技能图片
                        $matchInfo['playInfo'][$key][$k]['skill'][0]=$skill ?? '';
                        $matchInfo['playInfo'][$key][$k]['skill'][1]=$skill2 ?? '';
                        //装备图片
                        if(isset($v['itemcache']) && $v['itemcache']){
                            foreach ($v['itemcache'] as $key1=>&$val){
                                $matchInfo['playInfo'][$key][$k]['equipImg'][$key1]="https://static.wanplus.com/data/lol/item/11.2.1/".$val.".png";
                            }
                        }
                    }
                }
            }
        }
        $data['matchInfo']=$matchInfo;
        print_r($matchInfo);exit;
        $url= 'https://static.wanplus.com/data/lol/hero/square/'.$playData['bpList']['bans'][0][0]['cpherokey'].'.'.$playData['info']['heroImgSuffix'];
        echo $url;exit;
        print_r($playData['bpList']['bans']);exit;
        //print_r($playData);exit;
        $return = [];
        $data_list = ['pid'=>'player_id','pname'=>'player_name','score'=>'score'];
        foreach($playData['plList'] as $key => $player)
        {
            //player_id;
            $d = [];
            foreach($data_list as $k => $v)
            {
                $d[$v] = $player[$k];
            }
            $return[$player['pid']] = $d;
        }

        //gametype:1表示data2,2表示lol，6表示王者荣耀
        $gameTypes=[1,2,6];
        $totalPage=50;
        $data=[];{}
        foreach ($gameTypes as $val){
            for ($i=1;$i<=$totalPage;$i++){
                $url='https://www.wanplus.com/ajax/player/recent?isAjax=1&playerId=25474&gametype='.$val.'&page='.$i.'&heroId=0';
                echo $url;exit;
                $playData= $AjaxModel->getHistoryMatch($url);
                print_r($playData);exit;
            }
        }

        print_r(count($data));
        exit;
        $url='https://www.wanplus.com/ajax/player/recent?isAjax=1&playerId=25474&gametype=1&page=6&heroId=0';
        $playData = $AjaxModel->getHistoryMatch($url);//ajax 获取所有历史记录
        print_r($playData);exit;
        $client = new ClientServices();

        //攻略
        $client=new ClientServices();
        $data=curl_get('https://gicp.qq.com/wmp/data/js/v3/WMP_PVP_WEBSITE_NEWBEE_DATA_CH_V1.js');

        $url='https://apps.game.qq.com/wmp/v3.1/?p0=18&p1=searchNewsKeywordsList&page=1&pagesize=15&order=sIdxTime&_='.msectime();
        $refeerer = 'https://pvp.qq.com/web201605/searchResult.shtml';

        $headers = [
            'Referer'  => $refeerer,
            'Accept' => 'application/json',
        ];
        $data=$client->curlGet($url,'',$headers);//print_r($data['msg']['result']);exit;
        $result=$data['msg']['result'] ?? [];
        if($result){
            foreach ($result as $val){
                $detail_url='https://apps.game.qq.com/wmp/v3.1/public/searchNews.php?source=pvpweb_detail&p0=18&id='.$val['iNewsId'].'&&_='.msectime();
                $cdata=curl_get($detail_url);
                print_r($cdata);exit;
            }
        }
        //详情：$detail_url='https://apps.game.qq.com/wmp/v3.1/public/searchNews.php?source=pvpweb_detail&p0=18&id=497272&&_='.msectime();
        // $url='http://lol.kuai8.com/gonglue/index_1.html';

        $pageData = curl_get($url,$refeerer);print_r($pageData);exit;

        foreach($pageData['msg']['result'] as $val) {
            $detail_url='https://apps.game.qq.com/wmp/v3.1/public/searchNews.php?p0=18&source=web_pc&id='.$val['iNewsId'];
        }
        /* $client=new ClientServices();
         $data=curl_get($url);dd($data);
         $data=$client->curlGet($url);*/

        //for($i=0;$i<=32;$i++){
        // $m=$i+1;
        //$url='http://lol.kuai8.com/gonglue/index_'.$m.'.html';
        $ql = QueryList::get($url);
        $imgs=$ql->find('.Cont .news-list li img')->attrs('data-original');//print_r($imgs);exit;
        $data=$ql->rules([
            'title' => ['.con .tit', 'text'],
            'desc' => ['.con  .txt', 'text'],
            'link' => ['.img  a', 'href'],
            'img_url' => ['.img img', 'src'],
            'dtime' => ['.con  .time', 'text']
        ])->range('.Cont .news-list li')->queryData();
        foreach ($data as $key=>$val){
            $data = [
                "asign_to"=>1,
                "mission_type"=>'information',//攻略
                "mission_status"=>1,
                "game"=>'lol',
                "source"=>'kuai8',//
                'title'=>'',
                "detail"=>json_encode(
                    [
                        "url"=>$url,
                        "game"=>'lol',//英雄联盟
                        "source"=>'kuai8',//资讯
                        "title"=>$val['title'] ?? '',
                        "desc"=>$val['desc'] ?? '',
                        "img_url"=>$imgs[$key] ?? '',
                        "dtime"=>$val['dtime'] ?? '',

                    ]
                ),
            ];
        }
        //}//exit;
        print_r($data);exit;
        foreach ($data as &$val){
            $detail_url=$val['link'];
            $detail_ql=QueryList::get($detail_url);
            $content=$detail_ql->find('.article-detail .a-detail-cont')->html();
            $author=$detail_ql->find('.article-detail .a-detail-head span:eq(0)')->text();print_r($author);exit;
            $val['author']=$author ?? '';
            $val['content']=$content ?? '';
        }dd($data);
        //$links=$ql->find('.news-list li .con .tit')->texts()->all();//分页
        dd($data);
        $data=curl_get($url);

        $model=new DefaultConfig();
        $a=$model->getDefaultById(3);dd($a);
        $data=$this->kplInfo();dd($data);

        //$html=iconv('gb2312','utf-8',file_get_contents('https://pvp.qq.com/web201605/herodetail/191.shtml'));

        /*$client=new ClientServices();
        $data=$client->curlGet($url);
        dd($data);*/
        //$url='http://www.2cpseo.com/teams/lol/p-1';//分页
        /* $url='http://www.2cpseo.com/teams/lol/p-1';//分页
         $ql = QueryList::get($url);
         $links=$ql->find('.hot-teams-container a')->attrs('href')->all();//分页
         dd($links);*/
        /*  $res=$this->cpseoTeam();
          dd($res);*/
        /*$url='http://www.2cpseo.com/events/lol/p-1';//分页
        $ql = QueryList::get($url);
        $links=$ql->find('.versus a')->attrs('href')->all();//分页
        dd($links);*/
        $url='http://www.2cpseo.com/event/439';
        /*$infos=$this->cpseoTeam('http://www.2cpseo.com/teams/lol/p-1');
dd($infos);*/
        $ql = QueryList::get($url);
        $logo=$ql->find('.kf_roster_dec img')->attr('src');
        $logo='http://www.2cpseo.com'.$logo;
        $wraps=$ql->find('.text_wrap:eq(0) .text_2 p')->text();
        $wraps=explode("\n",$wraps);
        if($wraps){
            foreach ($wraps as $key=>$val){
                if(strpos($val,'英雄联盟：') !==false) {
                    $title=str_replace('英雄联盟：','',$val);
                }
                if(strpos($val,'开始时间：') !==false) {
                    $startTime=str_replace('开始时间：','',$val);

                }
                if(strpos($val,'结束时间：') !==false) {
                    $endTime=str_replace('结束时间：','',$val);
                }

            }
        }

        $baseInfo=[
            'logo'=>$logo,
            'title'=>$title ?? '',
            'start_time'=>$startTime ?? '',
            'end_time'=>$endTime ?? '',
            'game_id'=>1,//game: 1表示lol
        ];

        $tapType=$ql->find('.tranding_tab .nav-tabs li')->texts()->all();
        $pkTeam=[];
        if(!empty($tapType)){
            foreach ($tapType as $key=>&$val){
                $pkTeam[$key]['type']=$val;
                $pkTeam[$key]['teamInfo'] = $ql->rules([
                    'date_2' => ['.date_2', 'text'],
                    'opponents_dec' => ['.kf_opponents_dec  h6', 'texts'],
                    'dtime' => ['.kf_opponents_gols  p', 'text']
                ])->range('#home'.($key+1).' li')->queryData();
            }
        }
        $res['baseInfo']=$baseInfo ?? [];//赛事基本
        $res['pkTeam']=$pkTeam ?? [];//pk战队
        dd($res);


        $arrData=[];
        $status=0;
        if($matchInfo) {
            foreach ($matchInfo as $key=>&$val){
                $imgUrl=str_replace(array("background-image: url('","');background-size: cover;"),'',$logos[$key]);
                $val['img_url']=$imgUrl?? '';
                $arrData=explode('--',$val['dtime']);
                $curTime=date("Y.m.d");dd($curTime);
                $val['start_time']=trim($arrData[0]) ?? '';
                $val['end_time']=trim($arrData[1]) ?? '';

            }
        }

        dd($matchInfo);

        $logo=$ql->find('.lemma_pic img')->attr('src');
        $desc = $ql->find('.abstract ')->text();
        $baseInfosNames=$ql->find('.abstract_tbl tr .base-info-card-title')->texts()->all();
        $baseInfosValues=$ql->find('.abstract_tbl tr td .base-info-card-value')->texts()->all();
        $baseInfos=[];
        if($baseInfosNames){
            foreach ($baseInfosNames as $key=>$val){
                $baseInfos[$key]['name']=$val;
                $baseInfos[$key]['value']=delZzts($baseInfosValues[$key],$replace='展开');//去除特殊符号
            }
        }
        dd($baseInfos);
        //皮肤
        $skinImg = $ql->find('.catalog_wrap')->htmls();
        $skiArr=explode('|',$skinImg);
        $tempSkiArr=[];
        $skinData=[];
        if($skiArr) {
            foreach ($skiArr as $key=>&$val){
                $tempSkiArr=explode('&',$val);
                $smallImg='https://game.gtimg.cn/images/yxzj/img201606/heroimg/'.$item_id.'/'.$item_id.'-smallskin-'.($key+1).'.jpg';
                $bigImg='https://game.gtimg.cn/images/yxzj/img201606/heroimg/'.$item_id.'/'.$item_id.'-bigskin-'.($key+1).'.jpg';
                $skinData[$key]['smallImg']=$smallImg;//小图
                $skinData[$key]['bigImg']=$bigImg;//大图
                $skinData[$key]['name']=$tempSkiArr[0] ?? '';//皮肤名称
            }
        }
        //评分能力
        $baseText= $ql->find('.cover-list-text')->texts()->all();
        $baseBars= $ql->find('.cover-list-bar .ibar')->attrs('style');
        $scoreInfo=[];
        if($baseText){
            foreach ($baseText as $key=>$val){
                $scoreInfo[$key]['name']=$val;
                $baseBar=str_replace(['width:','%'],'',$baseBars[$key]);
                $scoreInfo[$key]['value']=$baseBar ?? 0;
            }
        }
        //背景故事
        $heroStory= $ql->find('#hero-story .pop-bd')->html();
        //英雄介绍
        $history=$ql->find('#history .pop-bd')->html();

        //技能介绍
        $baseText= $ql->find('.cover-list-text')->texts()->all();
        $skillInfo=[];
        $skillImg=$ql->find('.skill-info  .skill-u1 li img')->attrs('src')->all();

        // $(".skill-show .show-list").eq(4).find(".skill-name b").html();
        //http://game.gtimg.cn/images/yxzj/img201606/heroimg/105/10500.png
        //game.gtimg.cn/images/yxzj/img201606/heroimg/105/10500.png
        $skillName=$ql->find('.skill-show .show-list .skill-name')->texts()->all();
        $skillDesc=$ql->find('.skill-show .show-list .skill-desc')->htmls()->all();
        //第五个技能时
        $skillNo5Name = $ql->find('.skill-show .show-list:eq(4) .skill-name')->text();
        $skillNo5Desc= $ql->find('.skill-show .show-list:eq(4) .skill-desc')->text();
        if($skillNo5Name !=''){//超过五张图片特殊处理
            $skillNo5=$ql->find('.no5')->attr('data-img');
            array_push($skillImg,$skillNo5);
            array_push($skillName,$skillNo5Name);
            array_push($skillDesc,$skillNo5Desc);
        }

        $skillBaseInfo=[];
        if($skillImg){
            foreach ($skillImg as $key=>$val){
                if($val!='###'){
                    $skillBaseInfo[$key]['killImg']='http:'.$val;//技能图片
                    if($skillName[$key]) {
                        $names=explode('冷却值',$skillName[$key]);
                        $skillBaseInfo[$key]['name']=$names[0] ??'';
                        $times=explode('消耗',$names[1]);
                        $skillBaseInfo[$key]['cooling']='冷却值'.$times[0] ??'';
                        $skillBaseInfo[$key]['consume']='消耗'.$times[1] ??'';
                    }
                    $skillBaseInfo[$key]['skillDesc']=$skillDesc[$key];
                }
            }
        }
        $skillBaseInfo=array_values($skillBaseInfo);
        //铭文搭配建议
        //铭文id,这个关联必须先执行inscription 这个铭文脚本，而且必须保证ming_id 与下面的保存一致
        $suggListIds = $ql->find('.sugg-u1')->attr('data-ming');
        $suggTips = $ql->find('.sugg-tips')->text();//铭文描述
        $suggList =[
            'sugglistIds'=>$suggListIds,
            'suggTips'=>$suggTips,
        ];


        //技能加点建议
        $suggInfo2Names = $ql->find('.sugg-info2 .sugg-name')->htmls()->all();//名称
        $suggInfo2Imgs=$ql->find('.sugg-info2 .sugg-skill img')->attrs('src')->all();//名称
        $addSkills=[];
        $tempSuggInfos=[];
        if($suggInfo2Imgs){
            foreach ($suggInfo2Imgs as $key=>$val){
                $addSkills[$key]['killImg']='http:'.$val;//技能图片;
                $tempSuggInfos=explode('</b><span>',$suggInfo2Names[$key]);
                $addSkills[$key]['name']=str_replace('<b>','',$tempSuggInfos[0]);
                foreach ($skillBaseInfo as $v){
                    if($v['killImg']=='http:'.$val) {
                        $addSkills[$key]['desc']=$v['name'];
                    }
                }
            }
        }
        //召唤师技能
        $summonerSkill=[];
        $summonerSkillName=$ql->find('.sugg-info2 .sugg-name3 b')->text();//大标题
        $summonerSkillDesc=$ql->find('.sugg-info2 .sugg-name3 span')->text();//名称
        $summonerSkillId=$ql->find('.sugg-info2 #skill3')->attr('data-skill');//关联召唤师技能id(80115|80121)
        $summonerSkill=[
            'summonerSkillName'=>$summonerSkillName ?? '',
            'summonerSkillDesc'=>$summonerSkillDesc ?? '',
            'summonerSkillId'=>$summonerSkillId ?? '',
        ];
        //英雄关系:0:最佳搭档 1:压制英雄 2:被压制英雄 (英雄原有id 需要保存一个字段)
        $heroInfoBox=[];
        $heroHdTitle = $ql->find('.hero-info-box .hero-hd li')->texts()->all();//名称
        $heroInfo=$ql->find('.hero-info-box .hero-info')->htmls('src')->all();//名称
        if($heroInfo){
            foreach ($heroInfo as $k=>$val){
                $heroInfoBox[$heroHdTitle[$k]]=QueryList::html($val)->rules(array(
                    'logo' => array('img','src'),
                    'link' => array('a','href')
                ))->range('.hero-relate-list li')->queryData();
                foreach($heroInfoBox[$heroHdTitle[$k]] as $k2=>&$v2){
                    $v2['link']='https://pvp.qq.com/web201605/herodetail/'.$v2['link'];//链接
                    $v2['logo']='http:'.$v2['logo'];//英雄图片
                    $v2['desc']=QueryList::html($val)->find('.hero-list-desc p:eq('.$k2.')')->text();
                }
            }
        }
        //出装建议
        $equipBox=[];
        $equipItemIds=$ql->find('.equip-bd .equip-list ')->attrs('data-item')->all();//关联装备id
        $equipTips=$ql->find('.equip-bd .equip-tips')->texts()->all();//描述
        if($equipItemIds){
            foreach ($equipItemIds as $key=>$val){
                $equipBox[$key]['equipItemIds']=$val;
                $equipBox[$key]['equipTips']=$equipTips[$key];
            }
        }

        $res['skinData']=$skinData ?? [];//皮肤
        $res['scoreInfo']=$scoreInfo ?? [];//评分
        $res['heroStory']=$heroStory ?? '';//背景故事
        $res['history']=$history; //英雄介绍
        $res['skillBaseInfo']=$skillBaseInfo ?? [];//技能介绍
        $res['suggList']=$suggList ?? [];//铭文搭配建议
        $res['addSkills']=$addSkills ?? [];//技能加点建议
        $res['heroInfoBox']=$heroInfoBox ?? [];//英雄关系
        $res['equipBox']=$equipBox ?? [];//出装建议

        return $res;
    }

    //资讯
    public function kplInfo()
    {
        $iSubType = '330';//330=>活动,329=>赛事，

        $url = 'https://apps.game.qq.com/wmp/v3.1/?p0=18&p1=searchNewsKeywordsList&page=1&pagesize=15';
        $refeerer = 'Referer: https://pvp.qq.com/web201605/searchResult.shtml';
        $pageData = curl_get($url, $refeerer);
        $cdata=$pageData['msg']['result'] ?? [];
        $data=[];
        foreach ($cdata as $key=>$val){
            $refeerer_detail ='Referer: https://pvp.qq.com/web201605/newsDetail.shtml?G_Biz='.$val['iBiz'].'&tid='.$val['iNewsId'];
            $detail_url='https://apps.game.qq.com/wmp/v3.1/public/searchNews.php?source=pvpweb_detail&p0='.$val['iBiz'].'&id='.$val['iNewsId'];
            /* $detail_data = curl_get($detail_url, $refeerer_detail);
             if($detail_data['status']==0) {
                 $data[$key]=$detail_data['msg'] ?? [];
             }*/
        }

        return $data;
    }

    public function cpseoTeam($url='http://www.2cpseo.com/teams/lol/p-1'){
        $ql = QueryList::get($url);
        $links=$ql->find('.hot-teams-container a')->attrs('href')->all();
        $res=[];
        if($links){
            foreach ($links as $key=>$val){
                $ql = QueryList::get($val);
                $logo=$ql->find('.kf_roster_dec img')->attr('src');
                $aka=$ql->find('.kf_roster_dec .text span:eq(0)')->text();
                $wraps=$ql->find('.text_wrap .text_2 p')->text();
                $wraps=explode("\n",$wraps);
                foreach ($wraps as $val){
                    if(strpos($val,'地区') !==false) {
                        $area=str_replace('地区：','',$val);
                    }
                    if(strpos($val,'中文名称') !==false) {
                        $cname=str_replace('中文名称：','',$val);
                    }
                    if(strpos($val,'英文名称') !==false) {
                        $ename=str_replace('英文名称：','',$val);
                    }
                    if(strpos($val,'建队时间') !==false) {
                        $createTime=str_replace('建队时间：','',$val);
                    }
                }

                $intro=(isset($wraps[6]) && $wraps[6]) ? trim($wraps[6]):'';
                $baseInfo=[
                    'logo'=>'http://www.2cpseo.com'.$logo,
                    'aka'=>$aka,
                    'area'=>$area,
                    'cname'=>$cname,
                    'ename'=>$ename,
                    'create_time'=>$createTime,
                    'intro'=>$intro
                ];
                $teamListLink=$ql->find('.versus a')->attrs('href')->all();

                $res[$key]=[
                    'baseInfo'=>$baseInfo,
                    'teamListLink'=>$teamListLink
                ];

            }
        }

        return $res;
    }
    public function test()
    {
        $result_list = (new CollectResultModel())->getResult(100);
        print_R($result_list);

    }

}
