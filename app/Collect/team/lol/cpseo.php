<?php

namespace App\Collect\team\lol;

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
        $res = $this->cpseoTeam($url);
        if (!empty($res)) {
            foreach ($res as $key => $val) {
                $cdata[$key] = [
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
        var_dump($arr);
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
        $logo = $ql->find('.kf_roster_dec img')->attr('src');
        $aka = $ql->find('.kf_roster_dec .text span:eq(0)')->text();
        $wraps = $ql->find('.text_wrap .text_2 p')->text();
        $wraps = explode("\n", $wraps);
        foreach ($wraps as $key=>$val) {
            if (strpos($val, '地区') !== false) {
                $area = str_replace('地区：', '', $val);
            }
            if (strpos($val, '中文名称') !== false) {
                $cname = str_replace('中文名称：', '', $val);
            }
            if (strpos($val, '英文名称') !== false) {
                $ename = str_replace('英文名称：', '', $val);
            }
            if (strpos($val, '建队时间') !== false) {
                $createTime = str_replace('建队时间：', '', $val);
            }
            if(strpos($val,'简介') !==false) {
                $intro=$wraps[$key+1];
            }
        }

        $baseInfo = [
            'logo' => 'http://www.2cpseo.com' . $logo,
            'aka' => $aka,
            'area' => $area ?? '',
            'cname' => $cname ?? '',
            'ename' => $ename ?? '',
            'create_time' => $createTime ?? '',
            'intro' => $intro ?? ''
        ];
        $teamListLink = $ql->find('.versus a')->attrs('href')->all();

        $res = [
            'baseInfo' => $baseInfo,
            'teamListLink' => $teamListLink
        ];

        return $res;
    }
}
