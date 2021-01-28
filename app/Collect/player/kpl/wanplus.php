<?php

namespace App\Collect\player\kpl;

use App\Libs\AjaxRequest;
use QL\QueryList;

class wanplus
{
    protected $data_map =
        [
            "player_name"=>['path'=>"name",'default'=>''],
            "cn_name"=>['path'=>"name",'default'=>''],
            "en_name"=>['path'=>"name",'default'=>''],
            "aka"=>['path'=>"aka",'default'=>''],
            "country"=>['path'=>"country",'default'=>''],
            "position"=>['path'=>"position",'default'=>''],
            "team_history"=>['path'=>"",'default'=>[]],
            "event_history"=>['path'=>'','default'=>[]],
            "stat"=>['path'=>'','default'=>[]],
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

        if (!empty($res)) {
            $res['logo'] = $logo;
            $res['position'] = $position;
            $res['team_id'] = $team_id;
            $res['current'] = $current;
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
        /*
        foreach($arr['content']['playData']['eventList'] as $key => $value)
        {
            $arr['content']['playData']['eventList'][$key]['start_date'] = date("Y-m-d",$value['starttime']);
            unset($arr['content']['playData']['eventList'][$key]['starttime']);
        }
        foreach($arr['content']['playData']['stateList']['usedheroes'] as $key => $value)
        {
            $arr['content']['playData']['stateList']['usedheroes'][$key]['kda'] = sprintf("%.4f",$value['kda']);
            $arr['content']['playData']['stateList']['usedheroes'][$key]['winrate'] = sprintf("%.4f",$value['winrate']);
        }
        */
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
        return $res;
    }
}
