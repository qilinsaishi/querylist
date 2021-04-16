<?php

namespace App\Collect\player\kpl;

use App\Models\MissionModel;
use App\Models\TeamModel;
use App\Services\MissionService as oMission;
use QL\QueryList;

class cpseo
{
    protected $data_map =
        [
            "player_name"=>['path'=>"nickname",'default'=>''],
            "cn_name"=>['path'=>"cn_name",'default'=>''],
            "en_name"=>['path'=>"en_name",'default'=>''],
            "aka"=>['path'=>"aka",'default'=>''],
            "country"=>['path'=>"area",'default'=>''],
            "position"=>['path'=>"position",'default'=>''],
            "team_history"=>['path'=>'','default'=>[]],
            "event_history"=>['path'=>'','default'=>[]],
            "stat"=>['path'=>'','default'=>[]],
            "team_id"=>['path'=>'team_id','default'=>0],
            "logo"=>['path'=>'logo','default'=>0],
            "original_source"=>['path'=>"",'default'=>"cpseo"],
            "site_id"=>['path'=>"site_id",'default'=>0],
            "description"=>['path'=>"intro",'default'=>""],
            "heros"=>['path'=>"goodAtHeroes",'default'=>""],
        ];
    public function collect($arr)
    {
        $cdata = [];
        $url = $arr['detail']['url'] ?? '';
        $team_id = $arr['detail']['team_id'] ?? -1;
        $current = $arr['detail']['current'] ?? 1;
        $res = $this->cpSeoPlayer($url,$team_id);

        if (!empty($res)) {
           // $res['team_id'] = $team_id;
            $res['current'] = $current;
            $cdata = [
                'mission_id' => $arr['mission_id'],//任务id
                'content' =>is_array($res) ? json_encode($res):[],
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
         * "logo":"http://www.2cpseo.com/storage/dj/November2019/f66ab9b7095729d8fc661f9a937d4efc.jpg", //队员logo
         * "nickname":"Chieftain",  //昵称
         * "real_name":"李在烨LEEJAEYEOP,Lee Jae-yub (이재엽)", //真名
         * "position":"打野",  //位置
         * "area":"韩国", //地区/国家
         * "goodAtHeroes":"",//擅长英雄
         * "birthday":"2000年11月16", //生日
         * "usedId":"",//曾用id
         * "intro":"2020-11-17，由韩国明星金希澈投资的LCK联赛战队hyFresh Blade今日官宣两名选手加入。" //简介
         * }
         */
        if($arr['content']['team_id']==-1){
            $ql = QueryList::get($arr['source_link']);
            $team_link= $ql->find('.intro-content p span a')->attr('href');
            $team_link=$team_link ?? '';
            if($team_link !=''){
                $site_id=intval(str_replace('http://www.2cpseo.com/team/','',$team_link));
                $teamInfo = (new TeamModel())->getTeamBySiteId($site_id,"cpseo","kpl");

                if(isset($teamInfo['team_id'])){//存在则赋值
                    $arr['content']['team_id']=$teamInfo['team_id'];
                }else{//不存在则创建战队任务
                    $team_infos = $ql->find('.az-main-right .affiliation-team .content p')->texts()->all();
                    $team_infos=$team_infos ?? [];
                    $team_data=[];
                    $location=$team_cn_name=$team_en_name=$established_date='';
                    //获取战队信息
                    if(count($team_infos)>0){
                        foreach ($team_infos as $v){
                            if (strpos($v, '地区：') !== false) {
                                $location = str_replace('地区：', '', $v);
                            }
                            if (strpos($v, '中文名称：') !== false) {
                                $team_cn_name = str_replace('中文名称：', '', $v);
                            }
                            if (strpos($v, '英文名称：') !== false) {
                                $team_en_name = str_replace('英文名称：', '', $v);
                            }
                            if (strpos($v, '建队时间：') !== false) {
                                $established_date = str_replace('建队时间：', '', $v);
                            }
                        }
                    }
                    $params = [
                        'game' => 'kpl',
                        'mission_type' => 'team',
                        'source_link' => $team_link,
                    ];
                    $missionModel = new MissionModel();
                    $missionCount = $missionModel->getMissionCount($params);
                    //过滤已经采集过的文章
                    $missionCount = $missionCount ?? 0;
                    if ($missionCount <= 0) {
                        $team_data['location']=$location ?? '';
                        $team_data['cn_name']=$team_cn_name ?? '';
                        $team_data['en_name']=$team_en_name ?? '';
                        $team_data['established_date']=$established_date ?? '';
                        $team_data['url']=$team_link ?? '';
                        $team_data['game']='kpl';
                        $team_data['source']='cpseo';
                        $adata = [
                            "asign_to" => 1,
                            "mission_type" => 'team',
                            "mission_status" => 1,
                            "game" =>'kpl',
                            "source" => 'cpseo',
                            "title" => $team_cn_name ?? '',
                            'source_link' => $team_link,
                            "detail" => json_encode($team_data),
                        ];
                        $insert = (new oMission())->insertMission($adata);
                        echo "player-kpl-cpseo-insert-team:" . $insert . ' lenth:' . strlen($adata['detail']) . "\n";
                    }

                    return false;
                }
            }
            return false;

        }
        if(!isset($arr['content']['name'])){
            $ql = QueryList::get($arr['source_link']);
            $arr['content']['name'] = $ql->find('.intro-content-block .intro-name')->text();
        }

        $t = explode("/",$arr['source_link']);
        $arr['content']['site_id'] = intval($t[count($t)-1]??0);
        $arr['content']['aka'] = explode(",",$arr['content']['real_name']);
        $arr['content']['nickname']=$arr['content']['nickname'] ?? $arr['content']['name'] ;
        $arr['content']['cn_name'] = $arr['content']['real_name'];
        $arr['content']['en_name'] = $arr['content']['nickname'];
        $arr['content']['player_name'] = $arr['content']['name'];
        //     '/^[\x7f-\xff]+$/' 全是中文

        $arr['content']['logo'] = getImage($arr['content']['logo']);
        $data = getDataFromMapping($this->data_map,$arr['content']);
        if($data['player_name']==''){
            $data['player_name']=$arr['content']['name'];
        }
        return $data;
    }
    //王者荣耀队员信息
    public function cpSeoPlayer($url,$team_id)
    {
        $baseInfo = [];
        $headers=get_headers($url,1);
        if(!preg_match('/200/',$headers[0])){
            return  [];
        }
        $ql = QueryList::get($url);
        $logo = $ql->find('.commonDetail-intro img')->attr('src');
        $name = $ql->find('.intro-content-block .intro-name')->text();
        $wraps = $ql->find('.intro-content p')->texts()->all();
        $team_link= $ql->find('.intro-content p span a')->attr('href');
        if($team_id==-1){
            $site_id=intval(str_replace('http://www.2cpseo.com/team/','',$team_link));
            $teamInfo = (new TeamModel())->getTeamBySiteId($site_id,"cpseo","kpl");
            if(isset($teamInfo['team_id'])){
                $team_id=$teamInfo['team_id'] ;
            }

        }

        $wraps=$wraps ?? [];
        $realname=$nickname=$position=$area=$intro=$goodAtHeroes=$birthday=$team_name='';

        if (count($wraps)>0) {

            foreach ($wraps as $key => $val) {
                if ((strpos($val, '昵称：') !== false) && strlen($val)<32) {
                    $nickname = str_replace('昵称：', '', $val);
                }
                if ((strpos($val, '所属战队：') !== false) && strlen($val)<32) {
                    $team_name = str_replace('所属战队：', '', $val);
                }
                if ((strpos($val, '真名：') !== false) && strlen($val)<28) {
                    $realname = str_replace('真名：', '', $val);
                }
                if ((strpos($val, '位置：') !== false) && strlen($val)<32) {
                    $position = str_replace('位置：', '', $val);
                }
                if ((strpos($val, '地区：') !== false) && strlen($val)<32) {
                    $area = str_replace('地区：', '', $val);
                }
                if (strpos($val, '简介：') !== false) {
                    $intro = $wraps[$key + 1];
                }
                if (strpos($val, '擅长英雄：') !== false) {
                    $goodAtHeroes = str_replace('擅长英雄：', '', $val);
                }
                if (strpos($val, '主玩英雄：') !== false) {
                    $goodAtHeroes = str_replace('主玩英雄：', '', $val);
                }
                if (strpos($val, '生日：') !== false) {
                    $birthday = str_replace('生日：', '', $val);
                }
                if (strpos($val, '曾用ID：') !== false) {
                    $usedId = str_replace('曾用ID：', '', $val);
                }

            }
        }

        if (strpos($name, '（') !== false) {
            $replace_str='（'.$team_name.'）';
            $name = str_replace($replace_str, '', $name);
        }
        $team_infos = $ql->find('.az-main-right .affiliation-team .content p')->texts()->all();
        $team_infos=$team_infos ?? [];
        $team_data=[];
        $location=$team_cn_name=$team_en_name=$established_date='';
        if(count($team_infos)>0){
            foreach ($team_infos as $v){
                if (strpos($v, '地区：') !== false) {
                    $location = str_replace('地区：', '', $v);
                }
                if (strpos($v, '中文名称：') !== false) {
                    $team_cn_name = str_replace('中文名称：', '', $v);
                }
                if (strpos($v, '英文名称：') !== false) {
                    $team_en_name = str_replace('英文名称：', '', $v);
                }
                if (strpos($v, '建队时间：') !== false) {
                    $established_date = str_replace('建队时间：', '', $v);
                }
            }
        }
        if($location !=''){
            $team_data['location']=$location;
        }
        if($team_cn_name !=''){
            $team_data['cn_name']=$team_cn_name;
        }
        if($team_en_name !=''){
            $team_data['en_name']=$team_en_name;
        }
        if($established_date !=''){
            $team_data['established_date']=$established_date;
        }
        /*if(count($team_data)>0){
            $teamModel=new TeamModel();
            $rt=$teamModel->updateTeam($team_id,$team_data);
        }*/
        $en_name=$ql->find('.commonDetail-intro .intro-name-block .intro-name')->text();

        $en_name = str_replace(array('('.$realname.')','（'.$realname.'）',''.$team_name.'.'), '', $en_name);


        $baseInfo = [
            'logo' => 'http://www.2cpseo.com' . $logo,
            'name' => $name ?? '',//名称
            'nickname' => $en_name ?? '',//昵称
            'real_name' => $realname ?? '',//真名
            'position' => $position ?? '',//位置
            'area' => $area ?? '',//地区
            'game'=>'kpl',//王者荣耀
            'goodAtHeroes' => $goodAtHeroes ?? '',//擅长英雄
            'birthday' => $birthday ?? '',//生日
            'usedId' => $usedId ?? '',//曾用ID
            'intro' => $intro ?? '',//
            'team_id'=>$team_id,
        ];
        return $baseInfo;
    }
}
