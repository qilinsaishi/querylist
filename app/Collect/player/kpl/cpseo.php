<?php

namespace App\Collect\player\kpl;

use QL\QueryList;

class cpseo
{
    protected $data_map =
        [
        ];
    public function collect($arr)
    {
        $cdata = [];
        $url = $arr['detail']['url'] ?? '';
        $res = $this->cpSeoPlayer($url);
        $team_id = $arr['detail']['team_id'] ?? '';
        $current = $arr['detail']['current'] ?? '';

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
        var_dump($arr);
    }
    //王者荣耀队员信息
    public function cpSeoPlayer($url)
    {
        $baseInfo = [];
        $ql = QueryList::get($url);
        $logo = $ql->find('.commonDetail-intro img')->attr('src');
        $name = $ql->find('.intro-content-block .intro-name')->text();
        $wraps = $ql->find('.intro-content p')->texts()->all();
        if ($wraps) {
            foreach ($wraps as $key => $val) {
                if (strpos($val, '昵称：') !== false) {
                    $nickname = str_replace('昵称：', '', $val);
                }
                if (strpos($val, '真名：') !== false) {
                    $realname = str_replace('真名：', '', $val);
                }
                if (strpos($val, '位置：') !== false) {
                    $position = str_replace('位置：', '', $val);
                }
                if (strpos($val, '地区：') !== false) {
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
            $replace_str='（'.$nickname.'）';
            $name = str_replace($replace_str, '', $name);
        }
        $baseInfo = [
            'logo' => 'http://www.2cpseo.com' . $logo,
            'name' => $name ?? '',//名称
            'nickname' => $nickname ?? '',//昵称
            'real_name' => $realname ?? '',//真名
            'position' => $position ?? '',//位置
            'area' => $area ?? '',//地区
            'game'=>'kpl',//王者荣耀
            'goodAtHeroes' => $goodAtHeroes ?? '',//擅长英雄
            'birthday' => $birthday ?? '',//生日
            'usedId' => $usedId ?? '',//曾用ID
            'intro' => $intro ?? '',//
        ];
        return $baseInfo;
    }
}
