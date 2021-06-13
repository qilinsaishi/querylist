<?php

namespace App\Collect\player\dota2;

use App\Libs\ClientServices;
use QL\QueryList;

class shangniu
{
    protected $data_map =
        [
            "player_name"=>['path'=>"player_name",'default'=>''],
            "cn_name"=>['path'=>"cn_name",'default'=>''],
            "en_name"=>['path'=>"en_name",'default'=>''],
            "aka"=>['path'=>"",'default'=>[]],
            "country"=>['path'=>"country",'default'=>''],
            "position"=>['path'=>"position",'default'=>''],
            "team_history"=>['path'=>'','default'=>[]],
            "event_history"=>['path'=>'event_history','default'=>[]],
            "stat"=>['path'=>'','default'=>[]],
            "team_id"=>['path'=>'team_id','default'=>0],
            "logo"=>['path'=>'player_logo','default'=>''],
            "original_source"=>['path'=>"",'default'=>"shangniu"],
            "site_id"=>['path'=>"site_id",'default'=>0],
            "description"=>['path'=>"",'default'=>""],
            "heros"=>['path'=>"",'default'=>""],
            "game"=>['path'=>"",'default'=>"dota2"],
            "player_stat"=>['path'=>"player_stat",'default'=>[]],

        ];
    public function collect($arr)
    {

        $cdata = [];
        $url = $arr['detail']['url'] ?? $arr['source_link'];
        $res= $arr['detail'] ?? [];
        if(isset($res['player_logo']) && strpos($res['player_logo'],'/mrtp.png') !==false){
            $res['player_logo']='http://qilingsaishi-01.oss-cn-hangzhou.aliyuncs.com/65d87d548f76492fab2eb9fea7f59ecda381650b.png';
        }
        $playerInfo = $this->shangniuPlayer($url,$res['site_id']);
        $res=array_merge($res,$playerInfo);

        if (is_array($res) && count($res)>0) {

            $cdata = [
                'mission_id' => $arr['mission_id'],//任务id
                'content' =>json_encode($res),
                'game' => $arr['game'] ?? 'dota2',//游戏类型
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
    public function process($arr)
    {
        /**
         * appearCount :出场次数
         * participationRate：参团率
         * minuteNumber ：分均补刀
         * hurtTransfRate：伤害转换率
         * bearRate：承伤占比
         * minuteOutput：分均输出
         */

        $redis = app("redis.connection");
        $arr['content']['player_logo'] = getImage($arr['content']['player_logo'],$redis);
        if( isset($arr['content']['event_history']) && count($arr['content']['event_history'])>0){
            $arr['content']['event_history'] = $this->processImg($arr['content']['event_history'],$redis);
        }
        $data = getDataFromMapping($this->data_map,$arr['content']);

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
    /**
     * 来自https://www.shangniu.cn
     * @param $url
     * @return array
     */
    public function shangniuPlayer($url,$playerId=0)
    {
        $res = [];
        $client = new ClientServices();
        $content=file_get_contents($url);
        //================================映射数据============================================
        $bodyStart = strpos($content, '(function(');
        $bodyEnd = strpos($content, '){return {layout');
        $functionIndexs = substr($content, $bodyStart, $bodyEnd - $bodyStart);
        $functionIndexs = str_replace(array('(function(', ')'), '', $functionIndexs);

        $functionIndexs=explode(',',$functionIndexs);
        //映射反转
        $functionIndexs = array_flip($functionIndexs);

        $serverStart = 'serverRendered:';
        $playerInfoStart=strpos($content, $serverStart);
        $playerInfoEnd=strripos($content, '));</script><script');
        $playerInfoString=substr($content,$playerInfoStart+strlen($serverStart),$playerInfoEnd-$playerInfoStart);
        $playerInfoString=substr($playerInfoString,strpos($playerInfoString,'}}(')+3);
        $playerInfoData=explode(',',$playerInfoString);

        foreach ($functionIndexs as $functionIndexKey=>$functionIndexValue){
            $functionIndexs[$functionIndexKey]=trim($playerInfoData[$functionIndexValue],'"');
        }
        //================================映射数据============================================

        $playerStatStart=strpos($content,'playerStatInfo:');

        $playerStatEnd=strpos($content,'userHeroList');
        $playerStatContent=substr($content,$playerStatStart,$playerStatEnd-$playerStatStart);
        $playerStatContent=str_replace(array('playerStatInfo:{'),'',$playerStatContent);
        $playerStatContentList=explode(',',$playerStatContent);

        $tournamentId=0;
        if(is_array($playerStatContentList) && count($playerStatContentList)>0){
            foreach ($playerStatContentList as $playerStatContentInfo){
                if(strpos($playerStatContentInfo,'tournamentId:')!==false){
                    $tournamentId=str_replace('tournamentId:','',$playerStatContentInfo);
                    if(!is_numeric($tournamentId)){
                        $tournamentId=$functionIndexs[$tournamentId];
                    }
                }
            }
        }

        //===========================赛事战队统计数据=========================================
        $shangniu_url='https://www.shangniu.cn/api/game/user/player/getPcPlayerStatBasicInfo?gameType=dota&tournamentId='.$tournamentId.'&playerId='.$playerId;
        $shangniu_headers = ['referer' => $url];
        $getTournamentPlayerStatInfo= $client->curlGet($shangniu_url, [],$shangniu_headers);
        $playerStat=$getTournamentPlayerStatInfo['body'] ?? [];
        /**
         * appearCount :出场次数
         * participationRate：参团率
         * minuteNumber ：分均补刀
         * hurtTransfRate：伤害转换率
         * bearRate：承伤占比
         * minuteOutput：分均输出
         */

        if(isset($playerStat['id'])){
            unset($playerStat['id']);
        }
        if(isset($playerStat['version'])){
            unset($playerStat['version']);
        }
        if(isset($playerStat['createdTime'])){
            unset($playerStat['createdTime']);
        }
        if(isset($playerStat['updatedTime:'])){
            unset($playerStat['updatedTime:']);
        }

        //===========================赛事战队统计数据=========================================


        $start=strpos($content,'<div class="wrap-box">');
        $end=strpos($content,'<footer class="footer sn-footer">');
        $html=substr($content,$start,$end-$start);
        //================队员基本信息=================================
        $playerInfo = QueryList::html($html)->rules([
            "descList" => [".player-info-new .desc-list .item","texts"],
        ])->query(function ($item){
            //print_r($item['descList']);exit;
            $cn_name=$en_name=$country='';
            $patten = '/([\x{4e00}-\x{9fa5}]+)/u';
            if(isset($item['descList']) && count($item['descList'])>0){
                foreach($item['descList'] as $descInfo){
                    if(strpos($descInfo,'真实姓名') !==false){
                        $real_name=str_replace('真实姓名：','',$descInfo);
                        if(preg_match($patten, $real_name)){
                            $cn_name=$real_name ?? '';
                        }else{
                            $en_name=$real_name ?? '';
                        }

                    }
                    if(strpos($descInfo,'国籍：')!==false){
                        $country=str_replace('国籍：','',$descInfo);
                    }
                }
                unset($item['descList']);
            }
            $item['country']=$country;
            $item['en_name']=$en_name;
            $item['cn_name']=$cn_name;
            return $item;
        })->getData();
        $playerInfo=$playerInfo->all();

        //================战队基本信息=================================
        //==========================比赛记录==========================
        $playerMatchList= QueryList::html($html)->rules([
            "scoreWrapper" => [".scoreWrapper ","text"],
            "match_time" => [".td:eq(1)","text"],
            "game_bo" => [".td:eq(2)","text"],
            "vsTeam" => [".vsTeam","html"],
            "hero_img" => [".td:eq(4) img","src"],
            "kda" => [".td:eq(5)","text"],
            "equipmentsListHtml" => [".td:eq(6)","html"],
            "match_id" => [".td:eq(7) a","href"],
        ])->range('.center-box .match-list .PlayerMatchList .tbody .row')->query(function ($item){
            $teamNames=QueryList::html($item['vsTeam'])->find('.teamName')->texts()->all();
            $teamImgs=QueryList::html($item['vsTeam'])->find('img')->attrs('src')->all();
            $vsTeam=[];
            if(is_array($teamNames)&& count($teamNames)>0){
                foreach ($teamNames as $teamKey=>$teamValue){
                    $vsTeam[$teamKey]=[
                        'team_name'=>$teamValue,
                        'team_img'=>$teamImgs[$teamKey] ?? ''
                    ];
                }
            }
            $item['teamList']=$vsTeam ?? [];

            unset($item['vsTeam']);
            $equipmentsList=[];
            $equipmentsListImgs=QueryList::html($item['equipmentsListHtml'])->find('img')->attrs('src')->all();
            $equipmentsListTitles=QueryList::html($item['equipmentsListHtml'])->find('img')->attrs('alt')->all();
            if(is_array($equipmentsListImgs) && count($equipmentsListImgs)>0){
                foreach ($equipmentsListImgs as $equipmentKey=>$equipmentValue){
                    $equipmentsList[$equipmentKey]=[
                        'equipment_name'=>$equipmentsListTitles[$equipmentKey] ?? '',
                        'equipment_img'=>$equipmentValue ?? ''
                    ];
                }
            }
            $item['equipmentsList']=$equipmentsList ?? [];
            if(isset($item['equipmentsListHtml'])){
                unset($item['equipmentsListHtml']);
            }
            $item['match_id']=str_replace(array('/esports/dota-live-','.html'),'',$item['match_id']);

            return $item;
        })->getData();
        $playerMatchList=$playerMatchList->all();


        //==========================比赛记录==========================

        $res=[
            'cn_name'=>$playerInfo['cn_name'] ?? '',
            'country'=>$playerInfo['country'] ?? '',
            'en_name'=>$playerInfo['en_name'] ?? '',
            'player_stat'=>$playerStat ?? [],
            'event_history'=>$playerMatchList ??[]
        ];

        return $res;
    }
}
