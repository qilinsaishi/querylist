<?php

namespace App\Collect\player\lol;

use App\Models\TeamModel;
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
        ];

    public function collect($arr)
    {
        $cdata = [];
        $url = $arr['detail']['url'] ?? '';
        $team_id = $arr['detail']['team_id'] ?? '';
        $current = $arr['detail']['current'] ?? '';
        $res = $this->cpSeoPlayer($url,$team_id);


        if (!empty($res)) {
            $res['team_id'] = $team_id;
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
        $t = explode("/",$arr['source_link']);
        $arr['content']['site_id'] = intval($t[count($t)-1]??0);
        $arr['content']['aka'] = explode(",",$arr['content']['real_name']);
        $arr['content']['cn_name'] = $arr['content']['real_name'];
        //     '/^[\x7f-\xff]+$/' 全是中文
        $arr['content']['en_name'] = $arr['content']['en_name'];
        $arr['content']['logo'] = getImage($arr['content']['logo']);
        $data = getDataFromMapping($this->data_map,$arr['content']);
        return $data;
    }
    //王者荣耀
    public function cpSeoPlayer($url,$team_id)
    {
        //判断url是否有效
        //$headers=get_headers($url,1);
       /* if(!preg_match('/200/',$headers[0])){
            return  [];
        }*/
        $baseInfo = [];
        $ql = QueryList::get($url);
        $logo = $ql->find('.commonDetail-intro .intro-left img')->attr('src');
        $logo='http://www.2cpseo.com' . $logo;

        $wraps = $ql->find('.commonDetail-intro .intro-content p')->texts()->all();
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
                if ((strpos($val, '真名：') !== false) && strlen($val)<32) {
                    $realname = str_replace('真名：', '', $val);
                }
                if ((strpos($val, '位置：') !== false) && strlen($val)<32) {
                    $position = str_replace('位置：', '', $val);
                }
                if ((strpos($val, '地区：') !== false) && strlen($val)<32) {
                    $area = str_replace('地区：', '', $val);
                }
                if (strpos($val, '简介：') !== false) {
                    $intro = $wraps[$key + 1] ?? '';
                }
                if (strpos($val, '擅长英雄：') !== false) {
                    $goodAtHeroes = str_replace('擅长英雄：', '', $val);
                }
                if (strpos($val, '生日：') !== false) {
                    $birthday = str_replace('生日：', '', $val);
                }
                if (strpos($val, '曾用ID：') !== false) {
                    $usedId = str_replace('曾用ID：', '', $val);
                }

            }
        }

        $team_infos = $ql->find('.az-main-right .affiliation-team .content p')->texts()->all();
        $team_infos=$team_infos ?? [];
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
        $team_data=[];
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

        if(count($team_data)>0){
            $teamModel=new TeamModel();
            $rt=$teamModel->updateTeam($team_id,$team_data);
        }
        $en_name=$ql->find('.commonDetail-intro .intro-name-block .intro-name')->text();
        $en_name = str_replace(array('('.$realname.')','（'.$realname.'）',''.$team_name.'.'), '', $en_name);
        $baseInfo = [
            'logo' =>$logo,
            'nickname' => $nickname ?? '',
            'real_name' => $realname ?? '',
            'en_name'=>$en_name ?? '',
            'position' => $position ?? '',
            'area' => $area ?? '',
            'goodAtHeroes' => $goodAtHeroes ?? '',
            'birthday' => $birthday ?? '',
            'usedId' => $usedId ?? '',
            'intro' => $intro ?? '',
        ];
        return $baseInfo;
    }
}
