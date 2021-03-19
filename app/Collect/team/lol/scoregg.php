<?php

namespace App\Collect\team\lol;

use QL\QueryList;

class scoregg
{
    protected $data_map =
        [
            "team_name"=>['path'=>"team_name",'default'=>''],
            "en_name"=>['path'=>"en_name",'default'=>''],
            "aka"=>['path'=>"","default"=>""],
            "location"=>['path'=>"","default"=>"未知"],
            "established_date"=>['path'=>"",'default'=>"未知"],
            "coach"=>['path'=>"",'default'=>"暂无"],
            "logo"=>['path'=>"team_image",'default'=>''],
            "description"=>['path'=>"",'default'=>"暂无"],
            "race_stat"=>['path'=>"raceStat",'default'=>[]],
            "original_source"=>['path'=>"",'default'=>"scoregg"],
            "site_id"=>['path'=>"team_id",'default'=>0],
            "honor_list"=>['path'=>"history_honor",'default'=>[]],
            "team_history"=>['path'=>"team_history",'default'=>[]],
        ];
    public function collect($arr)
    {
        $cdata = [];
        $res=[];
        $url = $arr['detail']['team_url'] ?? '';
        $teamInfo=$this->getScoreggInfo($url);
        $res = $url = $arr['detail'] ?? [];
        $res=array_merge($res,$teamInfo);
        if (count($res) >0) {
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
        /*[team_id] => 5
        [team_name] => RNG
        [team_image] => https://img1.famulei.com/z/2373870/p/201/0815415242776_100X100.png //战队图片
        [KDA] => 5.0
        [MACTH_TIMES] => 13  //比赛场次
        [AVERAGE_TIME] => 00:31:57  //场均时长
        [FIRSTBLOODKILL] => 58.1   //首次杀戮(一血率%)
        [AVERAGE_KILLS] => 15.6 //场均击杀 人数
        [AVERAGE_ASSISTS] => 39.2 //平均助攻
        [AVERAGE_DEATHS] => 10.8  //场均死亡
        [AVERAGE_CHAMPIONS] => 61828.5  //场均伤害）
        [MINUTE_OUTPUT] => 1994.5 //每分钟输出
        [MINUTE_HITS] => 35.1  //每分钟补刀
        [AVERAGE_MONEY] => 60248.4  //场均经济
        [MINUTE_MONEY] => 1943.5  //每分钟输出
        [AVERAGE_SMALLDRAGON] => 2.7 //场均小龙
        [SMALLDRAGON_RATE] => 57.4  //小龙控制率(%)
        [MINUTE_WARDSPLACED] => 3.7  //每分钟插眼数
        [MINUTE_WARDSKILLED] => 1.8  //每分钟拆眼熟
        [AVERAGE_TOWER_SUCCESS] => 7.5 //场均推塔
        [AVERAGE_TOWER_FAIL] => 3.8 //场均被推塔数
        [AVERAGE_BIGDRAGON] => 1.0  //场均大龙
        [BIGDRAGON_RATE] => 68.9  大龙控制率（%）
        [update_time] => 1615865101
        [win] => 11  //胜利
        [los] => 2   //失败
        [VICTORY_RATE] => 84.6 //胜率
        [RESULT_TIMES] => 31 比赛(总)场次
        [f_score] => 188.0  //战力评分
        [total_kills] => 485 总击杀数
        [total_deaths] => 335 //总死亡数
        [total_SMALLDRAGON] => 85 //总小龙数
        [total_BIGDRAGON] => 31 //总大龙数
        [total_assists] => 1216 //总击助攻数
        [cn_name]=>$cn_name ?? '',//中文名
        [team_history]=>$content ?? '',//战队历程
        [play_list]=> [
                contract_end_time: "1669046400"
                country_image: "https://img1.famulei.com/z/0/p/1610/1213053470757.png"
                join_time: "2020-06-17"
                nickname: "GALA"
                playerID: "1689"
                player_image: "https://img1.famulei.com/z/6328686/p/201/0818350594029.png"
                position_name: "ADC"
        ],//现役队员列表
        [history_honor]=>[//历史荣誉
                match_time => 2018-10-20 //时间
                ranking => 5-8名 //荣誉/名次
                ranking_icon => https://img.scoregg.com/web_static/static/img/a40de7d.png //荣誉/名次icon
                t_image => https://img1.famulei.com/z/5688126/p/189/1620433552131.png //赛事图片
                t_name => S8世界总决赛//赛事
                team_a_image => https://img1.famulei.com/z/0/p/171/0411152029924_100X100.png //战队a图片
                team_name_a => RNG //战队a名称
                team_b_image => https://img1.famulei.com/z/0/p/1612/2915402013103_100X100.png //战队b图片
                team_name_b => G2 //战队b名称
                bonus => //奖金
                team_a_win => 2  //战队a比分
                team_b_win => 3 //战队b比分
        ],//荣誉信息数组*/
        //     '/^[\x7f-\xff]+$/' 全是中文
        if(!preg_match('/[\x7f-\xff]/', $arr['content']['team_name']))
        {
            $arr['content']['en_name'] = $arr['content']['team_name'];
        }

        $arr['content']['team_image'] = getImage($arr['content']['team_image']);
        $arr['content']['raceStat'] = ["win"=>intval($arr['content']['win']??0),"draw"=>0,"lose"=>intval($arr['content']['los']??0)];
        $arr['content']['team_history'] = json_encode($arr['content']['team_history']);

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
        $baseinfo=[
            'cn_name'=>$cn_name ?? '',//中文名
            'team_history'=>$content ?? '',//战队历程
            'play_list'=>$play_list ?? [],//现役队员
            'history_honor'=>$history_honor ?? [],//荣誉信息
        ];
        return $baseinfo;

    }
}
