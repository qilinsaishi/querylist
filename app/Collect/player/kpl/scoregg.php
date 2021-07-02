<?php

namespace App\Collect\player\kpl;
use App\Models\MissionModel;
use App\Models\PlayerModel;
use App\Models\TeamModel;
use App\Services\MissionService as oMission;
use App\Services\PlayerService;
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
            "player_stat"=>['path'=>'player_stat','default'=>[]],
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
        $team_id=$arr['detail']['team_id'] ?? 0;
        $player_id=$arr['detail']['player_id'] ?? 0;
        if($player_id >0){
            $teamInfo = $this->getScoreggInfo($url,$team_id);
            $res = $url = $arr['detail'] ?? [];
            $playerModel = new PlayerModel();
            $playerInfo = $playerModel->getPlayerBySiteId($res['player_id'], $res['game'], $res['source']);
            $res['team_id']=$res['team_id'] ?? ($playerInfo['team_id'] ?? 0);
            $res['current']=isset($playerInfo['team_id']) ?1:0;
            $res['player_name']=$res['player_name'] ?? ($playerInfo['player_name'] ?? '');
            $res['player_image']=$res['player_image'] ?? ($playerInfo['logo'] ?? '');
            $res = array_merge($res, $teamInfo);
            $player_stat=(new PlayerService())->getScoreggPlayerInfo($player_id);
            $res['player_stat']=$player_stat;
            if (count($res) > 0) {
                //处理战队采集数据
                $res['player_name'] =$res['player_name'] ?? '';
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
            echo "mission_id:".$arr['mission_id'] .',player_id:'.$player_id."\n";
        }


        return $cdata;
    }

    public function process($arr)
    {
        $redis = app("redis.connection");

        $qt = QueryList::get($arr['source_link']);
        $player_name=$qt->find('.right-content h2')->text();
        $arr['content']['player_name']=$player_name ?? $arr['content']['player_name'];
        $arr['content']['stat'] =
            getFieldsFromArray($arr['content'],"KDA,PLAYS_TIMES,OFFERED_RATE,AVERAGE_KILLS,AVERAGE_ASSISTS,AVERAGE_DEATHS,MINUTE_ECONOMIC,MINUTE_HITS,MINUTE_DAMAGEDEALT,DAMAGEDEALT_RATE,MINUTE_DAMAGETAKEN,DAMAGETAKEN_RATE,MINUTE_WARDSPLACED,MINUTE_WARDKILLED,MVP,win,los,VICTORY_RATE,total_kills,total_deaths,total_assists");
        if($arr['content']['current']!=1) {
            $teamInfo = (new TeamModel())->getTeamBySiteId($arr['content']['team_id'],"scoregg","lol");
            $arr['content']['team_id'] = $teamInfo['team_id']??0;
        }
        if($arr['content']['team_id']>0)
        {
            $arr['content']['player_image'] = getImage($arr['content']['player_image'],$redis);
            $patten = '/([\x{4e00}-\x{9fa5}]+)/u';
            if(isset($arr['content']['real_name']) && preg_match($patten, $arr['content']['real_name'])){
                $arr['content']['cn_name'] = $arr['content']['real_name'];
            }else{
                $arr['content']['cn_name'] = preg_match($patten, $arr['content']['player_name']) ? $arr['content']['player_name']:'';
            }
            if(isset($arr['content']['player_chinese_name']) && $arr['content']['player_chinese_name'] !=''){
                $arr['content']['cn_name']=$arr['content']['player_chinese_name'];
            }

            if(isset($arr['content']['real_name']) && !preg_match($patten, $arr['content']['real_name'])){
                $arr['content']['en_name'] = $arr['content']['real_name'];
            }else{
                $arr['content']['en_name'] = !preg_match($patten, $arr['content']['player_name']) ? $arr['content']['player_name']:'';
            }
            if(isset($arr['content']['player_chinese_name']) && $arr['content']['player_chinese_name'] !=''){
                $arr['content']['cn_name']=$arr['content']['player_chinese_name'];
            }
            $arr['content']['position'] = is_array($arr['content']['position'])?$arr['content']['position']['0']??"":$arr['content']['position'];
            $data = getDataFromMapping($this->data_map,$arr['content']);
            return $data;
        }

    }

    //获取战队scoregg战队详情
    public function getScoreggInfo($url,$team_id)
    {
        $qt = QueryList::get($url);
        $infos = $qt->find('.left-content .game-history .hero-info .info-item')->texts()->all();
        if (count($infos) > 0) {
            foreach ($infos as $val) {
                if (strpos($val, '国籍：') !== false) {
                    $country = str_replace('国籍： ', '', $val);
                }
                if (strpos($val, '队伍：') !== false) {
                    $patten = '/([\x{4e00}-\x{9fa5}]+)/u';
                    $name = str_replace('队伍：', '', $val);
                    if(preg_match($patten, $name)){
                        $team_data['cn_name']=trim($name);
                    }else{
                        $team_data['en_name']=trim($name);
                    }
                    if(count($team_data)>0 && isset($team_id)){
                        $teamModel=new TeamModel();
                        //$rt=$teamModel->updateTeam($team_id,$team_data);
                    }

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
            'history_honor' => $history_honor ?? [],
            'content' => $content,
        ];
        return $baseinfo;

    }
}
