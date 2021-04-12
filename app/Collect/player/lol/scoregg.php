<?php

namespace App\Collect\player\lol;

use App\Models\TeamModel;
use QL\QueryList;

class scoregg
{
    protected $data_map =
        [
            "player_name"=>['path'=>"player_name",'default'=>''],
            "cn_name"=>['path'=>"cn_name",'default'=>''],
            "en_name"=>['path'=>"en_name",'default'=>''],
            "aka"=>['path'=>"",'default'=>''],
            "country"=>['path'=>"country",'default'=>''],
            "position"=>['path'=>"position",'default'=>''],
            "team_history"=>['path'=>'','default'=>[]],
            "event_history"=>['path'=>'','default'=>[]],
            "stat"=>['path'=>'stat','default'=>[]],
            "team_id"=>['path'=>'team_id','default'=>0],
            "logo"=>['path'=>'player_image','default'=>0],
            "original_source"=>['path'=>"",'default'=>"scoregg"],
            "site_id"=>['path'=>"player_id",'default'=>0],
            "description"=>['path'=>"content",'default'=>""],
            ];

    public function collect($arr)
    {
        $cdata = [];
        $res = [];
        $url = $arr['detail']['player_url'] ?? '';
        $teamInfo = $this->getScoreggInfo($url);
        $res = $url = $arr['detail'] ?? [];
        $res = array_merge($res, $teamInfo);
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
            //处理战队采集数据
        }

        return $cdata;
    }

    public function process($arr)
    {
        /**
         * [tournament_id] => 197 //赛事id
         * [player_id] => 3771  //队员id
         * [player_name] => 救赎  //队员名称
         * [player_image] => https://img.scoregg.com/m/2373870/p/213/0913420918641.png  /队员头像
         * [team_id] => 338  //战队id
         * [team_name] => 北京WB  //战队名称
         * [team_image] => https://img.scoregg.com/z/554377/p/213/0115180837633_100X100.png  //战队名称
         * [position] => 辅助 //位置
         * [KDA] => 13.5
         * [PLAYS_TIMES] => 2 //玩家出场次数
         * [OFFERED_RATE] => 87.0 //参团率
         * [AVERAGE_KILLS] => 1.5 //场均击杀
         * [AVERAGE_ASSISTS] => 12.0 //场均助攻
         * [AVERAGE_DEATHS] => 1.0  //场均死亡
         * [MINUTE_ECONOMIC] => 452.1 //每分钟经济
         * [MINUTE_HITS] => 0.0  //每分钟补刀
         * [MINUTE_DAMAGEDEALT] => 1287.1 //每分钟输出
         * [DAMAGEDEALT_RATE] => 7.7  //输出占比
         * [MINUTE_DAMAGETAKEN] => 4525.9 //每分钟承受伤害
         * [DAMAGETAKEN_RATE] => 21.5  //承受伤害占比
         * [MINUTE_WARDSPLACED] => 0.0 //每分钟插眼数
         * [MINUTE_WARDKILLED] => 0.0 //每分钟拆眼熟
         * [update_time] => 1615932892
         * [MVP] => 0 //mvp次数
         * [player_chinese_name] => 张佳豪
         * [win] => 1  //胜利
         * [los] => 1 //失败
         * [VICTORY_RATE] => 50.0 //胜利率
         * [country_id] => 1 //国家id
         * [country_image] => https://img1.famulei.com/z/0/p/1610/1213053470757.png //国家图标
         * [f_score] => 99.7  //战力评分
         * [position_id] => 5
         * [total_kills] => 3 //总击杀数
         * [total_deaths] => 2 //总死亡数
         * [total_assists] => 24  //总助攻数
         * [player_url] => https://www.scoregg.com/big-data/player/3771?tournamentID=197&type=baike
         * [game] => kpl
         * [source] => scoregg
         * [country]=>国家
         * [birthday]=>出生日期
         * [status]=>所在队伍输出状态（主力）
         * [content]=>介绍
         * [history_honor]=>[//历史荣誉
         *      match_time => 2018-10-20 //时间
         *      ranking => 5-8名 //荣誉/名次
         *      ranking_icon => https://img.scoregg.com/web_static/static/img/a40de7d.png //荣誉/名次icon
         *      t_image => https://img1.famulei.com/z/5688126/p/189/1620433552131.png //赛事图片
         *      t_name => S8世界总决赛//赛事
         *      team_a_image => https://img1.famulei.com/z/0/p/171/0411152029924_100X100.png //战队a图片
         *      team_name_a => RNG //战队a名称
         *      team_b_image => https://img1.famulei.com/z/0/p/1612/2915402013103_100X100.png //战队b图片
         *      team_name_b => G2 //战队b名称
         *      team_a_win => 2  //战队a比分
         *      team_b_win => 3 //战队b比分
         * ]
         */
        $qt = QueryList::get($arr['source_link']);
        $player_name=$qt->find('.right-content h2')->text();
        $arr['content']['player_name']=$player_name ?? $arr['content']['player_name'];
        $teamInfo = (new TeamModel())->getTeamBySiteId($arr['content']['team_id'],"scoregg","lol");
        if(isset($teamInfo['team_id']))
        {
            /*$stat_arr_list = ['KDA','PLAYS_TIMES'];
            $arr['content'] = [];
            foreach($stat_arr_list as $key_name)
            {
                $arr['content']['stat'][$key_name] = $arr['content'][$key_name];
            }*/
            $arr['content']['player_image'] = getImage($arr['content']['player_image']);

            $arr['content']['team_id'] = $teamInfo['team_id'];
            $patten = '/([\x{4e00}-\x{9fa5}]+)/u';
            if(isset($arr['content']['real_name']) && preg_match($patten, $arr['content']['real_name'])){
                $arr['content']['cn_name'] = $arr['content']['real_name'];
            }else{
                $arr['content']['cn_name'] = preg_match($patten, $arr['content']['player_name']) ? $arr['content']['player_name']:'';
            }

            if(isset($arr['content']['real_name']) && !preg_match($patten, $arr['content']['real_name'])){
                $arr['content']['en_name'] = $arr['content']['real_name'];
            }else{
                $arr['content']['en_name'] = !preg_match($patten, $arr['content']['player_name']) ? $arr['content']['player_name']:'';
            }

            $arr['content']['position'] = is_array($arr['content']['position'])?$arr['content']['position']['0']??"":$arr['content']['position'];
            $data = getDataFromMapping($this->data_map,$arr['content']);
            return $data;
        }
        else
        {
            return false;
        }
    }

    //获取战队scoregg战队详情
    public function getScoreggInfo($url)
    {
        $qt = QueryList::get($url);
        $infos = $qt->find('.left-content .game-history .hero-info .info-item')->texts()->all();
        if (count($infos) > 0) {
            foreach ($infos as $val) {
                if (strpos($val, '国籍：') !== false) {
                    $country = str_replace('国籍： ', '', $val);
                }
                if (strpos($val, '姓名：') !== false) {
                    $real_name = str_replace('姓名： ', '', $val);
                }
                if (strpos($val, '出生：') !== false) {
                    $birthday = str_replace('出生：', '', $val);
                }
                if (strpos($val, '状态：') !== false) {
                    $status = str_replace('状态：', '', $val);
                }
                if (strpos($val, '位置：') !== false) {
                    if (strpos($val, 'TOP') !== false) {
                        $position = '上单';
                    } elseif (strpos($val, 'MID') !== false) {
                        $position = '中单';
                    } elseif (strpos($val, 'JUG') !== false) {
                        $position = '打野';
                    } elseif (strpos($val, 'ADC') !== false) {
                        $position = '下路';
                    } elseif (strpos($val, 'SUP') !== false) {
                        $position = '辅助';
                    }

                }
                if (strpos($val, '合同到期时间：') !== false) {
                    $contract_expire = str_replace('合同到期时间：', '', $val);
                }

            }
        }


        $content = $qt->find('.right-content .baike-content')->html();
        //荣誉历程
        $history_honor = $qt->rules(array(
            'match_time' => array('.td-item:eq(0)', 'text'),//时间
            'ranking' => array('.td-item:eq(1) ', 'text'),//荣誉/名词
            'ranking_icon' => array('.td-item:eq(1) img', 'src'),//荣誉/名词 rank-icon
            't_image' => array('.td-item:eq(2) img', 'src'),//赛事图片
            't_name' => array('.td-item:eq(2) ', 'text'),//赛事名称
            'team_a_image' => array('.td-item:eq(3) .team-a img', 'src'),//赛事图片
            'team_name_a' => array('.td-item:eq(3)  .team-a ', 'html'),//赛事名称
            'team_win' => array('.td-item:eq(3)  .vs-score ', 'text'),//赛事名称
            'team_b_image' => array('.td-item:eq(3)  .team-b img', 'src'),//赛事图片
            'team_name_b' => array('.td-item:eq(3)   .team-b ', 'html'),//赛事名称
        ))->range('.history-honor .article-list .article-table  .border-bottom')->queryData(function ($item) {
            $item['ranking'] = trim($item['ranking']);

            $tempNames_a = explode('alt="', $item['team_name_a']);
            if(isset($tempNames_a[1])){
                $tempNames_a = explode('" class="team-logo"', $tempNames_a[1]);
                $item['team_name_a'] = $tempNames_a[0] ?? '';
            }

            $tempWins = explode(' : ', $item['team_win']);
            $item['team_a_win'] = $tempWins[0] ?? 0;
            $item['team_b_win'] = $tempWins[1] ?? 0;
            $tempNames_b = explode('alt="', $item['team_name_b']);
            if(isset($tempNames_b[1])){
                $tempNames_b = explode('" class="team-logo"', $tempNames_b[1]);
                $item['team_name_b'] = $tempNames_b[0] ?? '';
            }

            unset($item['team_win']);

            return $item;
        });
        $birthday = substr($birthday, 0, 11);
        $baseinfo = [
            'country' => $country ?? '',//国家
            'real_name'=>$real_name ?? '',//姓名
            'birthday' => $birthday ?? '',//出生
            'status' => trim($status) ?? '',//主力状态
            'position' => $position ?? '',//位置
            'history_honor' => $history_honor ?? '',
            'content' => $content,
        ];
        return $baseinfo;

    }
}
