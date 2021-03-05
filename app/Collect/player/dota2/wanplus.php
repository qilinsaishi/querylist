<?php

namespace App\Collect\player\dota2;

use App\Libs\AjaxRequest;
use QL\QueryList;

class wanplus
{
    /**
     * 相关注释
     * country 地区/国家
     * aka  别名
     * name 队员名称
     * position  位置
     * main_img 队员主图
     * historys 曾役战队
     *
     * 下面是ajax请求 playerid队员id， gametype游戏类型 'eid' => -1表示该队员所有赛事，其他值对应赛事的胜率以及常用英雄
     * playData['eventList']  该队员所有赛事
     * playData['stateList']  该队员常用英雄以及胜率（注意）
     * appearedtimes生涯总战绩(总场次)：
     * wintimes生涯总战绩(胜场次)
     * killrate平均每局KDA(胜利场次)
     * deathrate 平均每局KDA(平局场次)
     * assistrate 平均每局KDA(失败场次)
     * usedheroes常用英雄列表*/
    protected $data_map =
        [
            "player_name"=>['path'=>"name",'default'=>''],
            "cn_name"=>['path'=>"name",'default'=>''],
            "en_name"=>['path'=>"name",'default'=>''],
            "aka"=>['path'=>"aka",'default'=>''],
            "country"=>['path'=>"country",'default'=>''],
            "position"=>['path'=>"position",'default'=>''],
            "team_history"=>['path'=>'historys','default'=>[]],
            "event_history"=>['path'=>'playData.eventList','default'=>[]],
            "stat"=>['path'=>'playData.stateList','default'=>[]],
            "team_id"=>['path'=>'team_id','default'=>0],
            "logo"=>['path'=>'logo','default'=>0],
            "original_source"=>['path'=>"",'default'=>"wanplus"],
            "site_id"=>['path'=>"site_id",'default'=>0],
        ];

    public function collect($arr)
    {
        $cdata = [];
        $url = $arr['detail']['url'] ?? '';
        $position = $arr['detail']['position'] ?? '';
        $logo = $arr['detail']['logo'] ?? '';
        $team_id = $arr['detail']['team_id'] ?? '';
        $current = $arr['detail']['current'] ?? '';
        $res = $this->getCollectData($url);
        if(count($res)>0){
            $title= $arr['title'] ?? '';
            if(isset($res['name']) && $res['name']){
                if (!empty($res)) {
                    $res['logo'] = $logo;
                    $res['position'] = $position;
                    $res['team_id'] = $team_id;
                    $res['current'] = $current;
                    $res['title'] = $title;
                    $cdata = [
                        'mission_id' => $arr['mission_id'],
                        'content' => json_encode($res),
                        'game' => $arr['game'],
                        'source_link' => $url,
                        'title' => $arr['detail']['title'] ?? '',
                        'mission_type' => $arr['mission_type'],
                        'source' => $arr['source'],
                        'status' => 1,
                        'update_time' => date("Y-m-d H:i:s")
                    ];

                }
            }
        }

        return $cdata;
    }

    public function process($arr)
    {
        $t = explode("/",$arr['source_link']);
        $arr['content']['site_id'] = intval($t[count($t)-1]??0);
        foreach($arr['content']['historys'] as $key => $value)
        {
            //起始时间格式化
            $t = explode("-",$value['history_time']);
            if(count($t)!=2)
            {
                $t2 = $t;
                unset($t2[count($t)-1]);
                $t[0] = implode(".",$t2);
                $t[1] = $t[count($t)-1];
            }
            $start_date = date("Y.m",strtotime(str_replace(".","-",$t[0].".01")));
            $end_date = date("Y.m",strtotime(str_replace(".","-",$t[1].".01")));
            $start_date = ($start_date==$t[0])?$start_date:"~";
            $end_date = ($end_date==$t[1])?$end_date:"~";
            $arr['content']['historys'][$key]['history_time'] = $start_date."-".$end_date;
        }
        if(isset($arr['content']['playData']['eventList']))
        {
            foreach($arr['content']['playData']['eventList'] as $key => $value)
            {
                $arr['content']['playData']['eventList'][$key]['start_date'] = date("Y-m-d",$value['starttime']);
                unset($arr['content']['playData']['eventList'][$key]['starttime']);
            }
        }
        if(isset($arr['content']['playData']['stateList']['usedheroes']))
        {
            foreach($arr['content']['playData']['stateList']['usedheroes'] as $key => $value)
            {
                $arr['content']['playData']['stateList']['usedheroes'][$key]['kda'] = sprintf("%.4f",$value['kda']);
                $arr['content']['playData']['stateList']['usedheroes'][$key]['winrate'] = sprintf("%.4f",$value['winrate']);
            }
        }
        $arr['content']['logo'] = getImage($arr['content']['logo']);
        $data = getDataFromMapping($this->data_map,$arr['content']);
        return $data;
    }

    /**
     * @param $url
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getCollectData($url)
    {
        $ql = QueryList::get($url);
        $infos = $ql->find('.f15')->texts();//胜/平/负(历史总战绩)
        $logo=$ql->find('.team_box .team_tba img')->attr('src');
        $res=[];
        if($infos->all()) {
            $country = $aka = $title = '';
            if (!empty($infos->all())) {//遍历该队员基本信息
                foreach ($infos->all() as $val) {
                    if (strpos($val, '名称') !== false) {
                        $title = str_replace('名称：', '', $val);
                    }
                    if (strpos($val, '别名') !== false) {
                        $aka = str_replace('别名：', '', $val);
                    }
                    if (strpos($val, '地区') !== false) {
                        $country = str_replace('地区：', '', $val);
                    }

                }
            }
            $res['country'] = $country;
            $res['aka'] = $aka;
            $res['name'] = $title;

            $playerid = $ql->find('#recent #id')->attr('value');//id
            $gametype = $ql->find('#recent #gametype')->attr('value');

            //曾役战队
            $history_times = $ql->find('.team-history  li .history-time')->texts()->all();//队员名称
            $history_teams = $ql->find('.team-history  li span')->texts()->all();//队员名称
            $historys = [];

            foreach ($history_times as $k => $val) {//格式化数据
                $temps = preg_replace("/(\s|\&nbsp\;|　|\xc2\xa0)/", " ", strip_tags($val));
                $history_time = preg_replace('# #', '', $temps);
                $historys[$k]['history_time'] = $history_time ?? '';
                $historys[$k]['history_team'] = $history_teams[$k] ?? '';

            }
            $res['historys'] = $historys;
            $eid=-1;//表示该队员的所有赛事
            $param = [
                'playerid' => $playerid,//队员id
                'gametype' => $gametype,//游戏类型
                'eid' => $eid//表示该队员的所有赛事
            ];
        }

        return $res;
    }
}
