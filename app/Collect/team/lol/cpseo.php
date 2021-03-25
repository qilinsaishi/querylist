<?php

namespace App\Collect\team\lol;

use QL\QueryList;

class cpseo
{
    protected $data_map =
        [
            "team_name"=>['path'=>"baseInfo.cname",'default'=>''],
            "en_name"=>['path'=>"baseInfo.ename",'default'=>''],
            "aka"=>['path'=>"baseInfo.aka","default"=>""],
            "location"=>['path'=>"baseInfo.area","default"=>"未知"],
            "established_date"=>['path'=>"baseInfo.create_time",'default'=>"未知"],
            "coach"=>['path'=>"",'default'=>"暂无"],
            "logo"=>['path'=>"baseInfo.logo",'default'=>''],
            "description"=>['path'=>"baseInfo.intro",'default'=>"暂无"],
            "race_stat"=>['path'=>"",'default'=>[]],
            "original_source"=>['path'=>"",'default'=>"cpseo"],
            "site_id"=>['path'=>"site_id",'default'=>0],
            "team_history"=>['path'=>"team_history",'default'=>"[]"],
        ];

    public function collect($arr)
    {
        $cdata = [];
        $url = $arr['detail']['url'] ?? '';
        $res = $this->cpseoTeam($url);
        if (!empty($res)) {
                $cdata= [
                    'mission_id' => $arr['mission_id'],//任务id
                    'content' => json_encode($res),
                    'game' => $arr['game'],//游戏类型
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
         * {
         * "baseInfo":{
         * "logo":"http://www.2cpseo.com/storage/images/83535051d36581d0642d59769fc162c0.png",//战队图片
         * "aka":"BRB(hyFresh Blade)",  //别名
         * "area":"韩国",
         * "cname":"BRB",//中文名称
         * "ename":"hyFresh Blade", //英文名称
         * "create_time":"",
         * "intro":"hyFresh Blade，简称BRB，是一支韩国英雄联盟战队，他们也叫 Brion Blade。"  //简介
         * },
         * "teamListLink":[//队员列表
         * "http://www.2cpseo.com/player/33",
         * "http://www.2cpseo.com/player/1947"
         * ]
         * }
         */
        $t = explode("/",$arr['source_link']);
        $arr['content']['site_id'] = intval($t[count($t)-1]??0);
        if(strlen($arr['content']['baseInfo']['area'])>10)
        {
            unset($arr['content']['baseInfo']['area']);
        }
        $arr['content']['baseInfo']['logo'] = getImage($arr['content']['baseInfo']['logo']);
        $data = getDataFromMapping($this->data_map,$arr['content']);
        return $data;
    }

    /**
     * 来自http://www.2cpseo.com
     * @param $url
     * @return array
     */
    public function cpseoTeam($url)
    {
        $res = [];
        $ql = QueryList::get($url);
        $logo = $ql->find('.commonDetail-intro .logo-block img')->attr('src');
        $logo='http://www.2cpseo.com' . $logo;
        $team_name=$ql->find('.commonDetail-intro .content .name')->text();
        $en_name=$ql->find('.commonDetail-intro .content .subname')->text();
        $description=$ql->find('.commonDetail-intro .intro-content-block .intro-content')->html();
        $baseInfo = [
            'logo' =>$logo,
            'game_id' => 1,//lol
            'game' => 'lol',
            'area' => '',
            'cname' => $team_name ?? '',
            'ename' => $en_name ?? '',
            'intro' => $description ?? ''
        ];
        $teamListLink = $ql->find('.commonDetail-content .l-m-team-member:eq(0) a')->attrs('href')->all();
        $res = [
            'baseInfo' => $baseInfo,
            'teamListLink' => $teamListLink
        ];
        return $res;
    }
    public function processMemberList($team_id,$arr)
    {
        $missionList = [];
        foreach($arr['content']['teamListLink'] as $member)
        {
            $t = explode('/',$member);
            $site_id = $t[count($t)-1];
            $mission = ['mission_type'=>"player",
                'mission_status'=>0,
                'source_link' => $member,
                'title'=>$t[count($t)-1],
                'detail'=>json_encode(['url'=>$member,
                    'name'=>$t[count($t)-1],
                    'position'=>"",
                    'logo'=>"",
                    'team_id'=>$team_id,
                    'current'=>1,
                    'site_id'=>$site_id
                ]),
            ];
            $missionList[] = $mission;
        }
        return $missionList;
    }
}
