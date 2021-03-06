<?php

namespace App\Collect\team\lol;

use App\Models\MissionModel;
use App\Services\TeamService;
use QL\QueryList;

class scoregg
{
    protected $data_map =
        [
            "team_name"=>['path'=>"team_name",'default'=>''],
            "en_name"=>['path'=>"en_name",'default'=>''],
            "cn_name"=>['path'=>"cn_name",'default'=>''],
            "aka"=>['path'=>"","default"=>[]],
            "location"=>['path'=>"","default"=>"未知"],
            "established_date"=>['path'=>"",'default'=>"未知"],
            "coach"=>['path'=>"",'default'=>"暂无"],
            "logo"=>['path'=>"team_image",'default'=>''],
            "description"=>['path'=>"description",'default'=>"暂无"],
            "race_stat"=>['path'=>"raceStat",'default'=>[]],
            "original_source"=>['path'=>"",'default'=>"scoregg"],
            "site_id"=>['path'=>"team_id",'default'=>0],
            "honor_list"=>['path'=>"history_honor",'default'=>[]],
            "team_history"=>['path'=>"",'default'=>[]],
            "team_stat"=>['path'=>"team_stat",'default'=>[]],

        ];
    public function collect($arr)
    {
        $cdata = [];
        $res=[];
        $team_id=$arr['detail']['team_id'] ?? 0;
        if($team_id >0){
            $url = $arr['detail']['team_url'] ?? '';
            $teamInfo=$this->getScoreggInfo($url);
            $res = $url = $arr['detail'] ?? [];
            $res['team_name']=$res['team_name'] ?? ($teamInfo['teamBaseInfo']['team_name']??'');
            $res['en_name']=$res['en_name'] ?? ($teamInfo['teamBaseInfo']['en_name']??'');
            $res['team_image']=$res['team_image'] ?? ($teamInfo['teamBaseInfo']['team_image']??'');
            $res['win']=$res['win'] ?? ($teamInfo['teamBaseInfo']['win']??'');
            $res['draw']=$res['draw'] ?? ($teamInfo['teamBaseInfo']['draw']??'');
            $res['lose']=$res['lose'] ?? ($teamInfo['teamBaseInfo']['lose']??'');
            unset($teamInfo['teamBaseInfo']);
            $res=array_merge($res,$teamInfo);

            $team_stat=(new TeamService())->getScoreggTeamInfo($res['team_id']);
            $res['team_stat']=$team_stat;
            if (count($res) >0) {
                //处理战队采集数据
                $res['en_name']=$res['en_name'] ?? '';
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

        }else{
            //失败
            (new MissionModel())->updateMission($arr['mission_id'], ['mission_status' => 3]);
            echo "mission_id:".$arr['mission_id'] .',team_id:'.$team_id."\n";
        }

        return $cdata;
    }
    public function process($arr)
    {
        $redis = app("redis.connection");

        $arr['content']['team_image'] = getImage($arr['content']['team_image']??"",$redis);
        $arr['content']['raceStat'] = ["win"=>intval($arr['content']['win']??0),"draw"=>intval($arr['content']['draw']??0),"lose"=>intval($arr['content']['lose']??0)];

        $data = getDataFromMapping($this->data_map,$arr['content']);

        return $data;
    }
    //获取战队scoregg战队详情
    public function getScoreggInfo($url){
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
            if(isset($tempNames_a[1])){
                $tempNames_a=explode('" class="team-logo"',$tempNames_a[1]);
                $item['team_name_a']=$tempNames_a[0] ?? '';
            }

            $tempWins=explode(' : ',$item['team_win']);
            $item['team_a_win']=$tempWins[0] ?? 0;
            $item['team_b_win']=$tempWins[1] ?? 0;
            $tempNames_b=explode('alt="',$item['team_name_b']);
            if(isset($tempNames_b[1])){
                $tempNames_b=explode('" class="team-logo"',$tempNames_b[1]);
                $item['team_name_b']=$tempNames_b[0] ?? '';
            }

            unset($item['team_win']);

            return $item;
        });
        //=======================================获取战队基本信息===============================
        $bodyHtml=$qt->find('body')->html();
        /////////////////////////////////////////////////////////
        $start = strpos($bodyHtml, '(function(');
        $end = strpos($bodyHtml, '){');
        $functionIndexs = substr($bodyHtml, $start, $end - $start);
        $functionIndexs = str_replace(array('(function(', ')'), '', $functionIndexs);
        $functionIndexs=explode(',',$functionIndexs);
        //映射反转
        $functionIndexs = array_flip($functionIndexs);
        $serverStart = '}}}}';
        $teamInfoStart=strpos($bodyHtml, $serverStart);
        $teamInfoEnd=strripos($bodyHtml, '));</script><script');
        $teamInfoString=substr($bodyHtml,$teamInfoStart+strlen($serverStart)+1,$teamInfoEnd-$teamInfoStart);
        $teamInfoString=str_replace(array('));<'),'',$teamInfoString);
        $teamInfoData=explode(',',$teamInfoString);

        foreach ($functionIndexs as $functionIndexKey=>$functionIndexValue){
            $functionIndexs[$functionIndexKey]=trim($teamInfoData[$functionIndexValue],'"');
        }
        //================================获取战队基本信息================================
        $teamBaseInfo=[];
        $teamBaseInfoStart=strpos($bodyHtml,'teamInfo:{');
        $teamBaseInfoEnd=strpos($bodyHtml,'teamPlayerList');
        $teamBaseInfoStrinig=substr($bodyHtml,$teamBaseInfoStart+strlen('teamInfo:{'),$teamBaseInfoEnd-$teamBaseInfoStart);
        $teamBaseInfoStrinig=str_replace('},teamPlayer','',$teamBaseInfoStrinig);
        $teamBaseInfoData=explode(',',$teamBaseInfoStrinig);
        if(is_array($teamBaseInfoData) && count($teamBaseInfoData)>0){
            foreach ($teamBaseInfoData as $teamBaseInfoDataInfo){
                if(strpos($teamBaseInfoDataInfo,'teamID:')!==false){
                    $teamID=str_replace('teamID:','',$teamBaseInfoDataInfo);
                    if(is_numeric($teamID)){
                        $teamBaseInfo['team_id']=$teamID;
                    }else{
                        $teamBaseInfo['team_id']=$functionIndexs[$teamID];
                    }

                }
                if(strpos($teamBaseInfoDataInfo,'name_en:')!==false){
                    $name_en=str_replace('name_en:','',$teamBaseInfoDataInfo);
                    if(is_numeric($name_en)){
                        $teamBaseInfo['en_name']=$name_en;
                    }else{
                        $teamBaseInfo['en_name']=$functionIndexs[$name_en];
                    }

                }
                if(strpos($teamBaseInfoDataInfo,'short_name:')!==false){
                    $short_name=str_replace('short_name:','',$teamBaseInfoDataInfo);
                    if(is_numeric($short_name)){
                        $teamBaseInfo['team_name']=$short_name;
                    }else{
                        $teamBaseInfo['team_name']=$functionIndexs[$short_name];
                    }

                }
                if(strpos($teamBaseInfoDataInfo,'team_image:')!==false){
                    $team_image=str_replace('team_image:','',$teamBaseInfoDataInfo);
                    if(is_numeric($team_image)){
                        $teamBaseInfo['team_image']=$team_image;
                    }else{
                        $teamBaseInfo['team_image']=$functionIndexs[$team_image];
                    }
                    $json_team_image='["'.$teamBaseInfo['team_image'].'"]';
                    $arr_team_image=json_decode($json_team_image,true);
                    $teamBaseInfo['team_image']=$arr_team_image[0] ?? '';

                }
                if(strpos($teamBaseInfoDataInfo,'total_win:')!==false){
                    $total_win=str_replace('total_win:','',$teamBaseInfoDataInfo);
                    if(is_numeric($total_win)){
                        $teamBaseInfo['win']=$total_win;
                    }else{
                        $teamBaseInfo['win']=$functionIndexs[$total_win];
                    }

                }
                if(strpos($teamBaseInfoDataInfo,'total_flat:')!==false){
                    $total_flat=str_replace('total_flat:','',$teamBaseInfoDataInfo);
                    if(is_numeric($total_flat)){
                        $teamBaseInfo['draw']=$total_flat;
                    }else{
                        $teamBaseInfo['draw']=$functionIndexs[$total_flat];
                    }

                }
                if(strpos($teamBaseInfoDataInfo,'total_lose:')!==false){
                    $total_lose=str_replace('total_lose:','',$teamBaseInfoDataInfo);
                    if(is_numeric($total_lose)){
                        $teamBaseInfo['lose']=$total_lose;
                    }else{
                        $teamBaseInfo['lose']=$functionIndexs[$total_lose];
                    }

                }

            }

        }

        //================================获取战队基本信息================================

        $baseinfo=[
            'cn_name'=>$cn_name ?? '',//中文名
            'description'=>$content ?? '',//战队历程
            //'play_list'=>$play_list ?? [],//现役队员
            'history_honor'=>$history_honor ?? [],//荣誉信息
            'teamBaseInfo'=>$teamBaseInfo ?? [],
        ];
        return $baseinfo;

    }
}
