<?php

namespace App\Collect\player\lol;

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

    public function cpSeoPlayer($url)
    {
        $baseInfo = [];
        $ql = QueryList::get($url);
        $logo = $ql->find('.kf_roster_dec:eq(0) img')->attr('src');
        $wraps = $ql->find('.text_wrap:eq(0) .text_2 p')->text();
        $wraps = explode("\n", $wraps);
        if ($wraps) {
            foreach ($wraps as $key => $val) {
                if (strpos($val, '昵称：') !== false) {
                    $nickname = trim($val, '昵称：');
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
                if (strpos($val, '生日：') !== false) {
                    $birthday = str_replace('生日：', '', $val);
                }
                if (strpos($val, '曾用ID：') !== false) {
                    $usedId = str_replace('曾用ID：', '', $val);
                }

            }
        }
        $baseInfo = [
            'logo' => 'http://www.2cpseo.com' . $logo,
            'nickname' => $nickname ?? '',
            'real_name' => $realname ?? '',
            'position' => $position ?? '',
            'area' => $area ?? '',
            'goodAtHeroes' => $goodAtHeroes ?? '',
            'birthday' => $birthday ?? '',
            'usedId' => $usedId ?? '',
            'intro' => $intro ?? ''
        ];
        return $baseInfo;
    }
}
