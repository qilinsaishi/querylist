<?php

namespace App\Collect\team\dota2;

use App\Libs\AjaxRequest;

class wanplus
{
    protected $data_map =
        [
            "team_name"=>['path'=>"title",'default'=>''],
            "en_name"=>['path'=>"",'default'=>''],
            "aka"=>['path'=>"aka","default"=>""],
            "location"=>['path'=>"country","default"=>"未知"],
            "established_date"=>['path'=>"",'default'=>"未知"],
            "coach"=>['path'=>"",'default'=>"暂无"],
            "logo"=>['path'=>"logo",'default'=>''],
            "description"=>['path'=>"",'default'=>"暂无"],
            "race_stat"=>['path'=>"raceStat",'default'=>[]],
            "original_source"=>['path'=>"",'default'=>"wanplus"],
            "site_id"=>['path'=>"site_id",'default'=>0],
            "team_history"=>['path'=>"",'default'=>[]],
        ];
    public function collect($arr)
    {
        $return = [];
        $url = $arr['detail']['url'] ?? '';
        $ajaxRequest=new AjaxRequest();
        $res = $ajaxRequest->getCollectWanplusTeam($url);
        $title= $arr['detail']['title'] ?? '';
        $cdata = [];
        if (!empty($res))
        {   $res['title']=$title;
            //处理战队采集数据
            $cdata = [
                'mission_id' => $arr['mission_id'],
                'content' => json_encode($res),
                'game' => $arr['game'],
                'source_link'=>$url,
                'title'=>$arr['detail']['title'] ?? '',
                'mission_type'=>$arr['mission_type'],
                'source'=>$arr['source'],
                'status' => 1,
            ];
            //处理战队采集数据

        }
        return $cdata;

    }
    public function process($arr)
    {
        $t = explode("/",$arr['source_link']);
        $arr['content']['site_id'] = intval($t[count($t)-1]??0);
        //处理胜平负
        $t = explode("/",$arr['content']['military_exploits']??'');
        $arr['content']['raceStat'] = ["win"=>intval($t[0]??0),"draw"=>intval($t[1]??0),"lose"=>intval($t[2]??0)];
        $arr['content']['logo'] = getImage($arr['content']['logo']);
        $data = getDataFromMapping($this->data_map,$arr['content']);
        return $data;
    }
    public function processMemberList($team_id,$arr)
    {
        $missionList = [];
        if(isset($arr['content']['cur_team_members']))
        {
            foreach($arr['content']['cur_team_members'] as $member)
            {
                $t = explode('/',$member['link_url']);
                $site_id = $t[count($t)-1];
                $mission = ['mission_type'=>"player",

                    'mission_status'=>0,
                    'title'=>$member['name'],
                    'source_link' => $member['link_url'],
                    'detail'=>json_encode(['url'=>$member['link_url'],
                        'name'=>$member['name'],
                        'position'=>$member['position']??"",
                        'logo'=>$member['main_img'],
                        'team_id'=>$team_id,
                        'current'=>1,
                        'site_id'=>$site_id
                    ]),
                ];
                $missionList[] = $mission;
            }
        }
        if(isset($arr['content']['old_team_members']))
        {
            foreach($arr['content']['old_team_members'] as $member)
            {
                $t = explode('/',$member['link_url']);
                $site_id = $t[count($t)-1];
                $mission = ['mission_type' => "player",
                    'mission_status' => 0,
                    'title'=>$member['name'],
                    'source_link' => $member['link_url'],
                    'detail' => json_encode(['url' => $member['link_url'],
                        'name' => $member['name'],
                        'position' => $member['position']??"",
                        'logo' => $member['main_img'],
                        'team_id' => $team_id,
                        'current' => 0,
                        'site_id'=>$site_id
                    ]),
                ];
                $missionList[] = $mission;
            }
        }
        return $missionList;
    }
}
