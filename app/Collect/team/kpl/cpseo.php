<?php

namespace App\Collect\team\kpl;

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
                $cdata = [
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
            if (strpos($val, '地区：') !== false) {
                $area = str_replace('地区：', '', $val);
            }
            if (strpos($val, '中文名称：') !== false) {
                $cname = str_replace('中文名称：', '', $val);
            }
            if (strpos($val, '英文名称：') !== false) {
                $ename = str_replace('英文名称：', '', $val);
            }
            if (strpos($val, '建队时间：') !== false) {
                $createTime = str_replace('建队时间：', '', $val);
            }
            if(strpos($val,'简介：') !==false) {
                $intro=$wraps[$key+1] ?? '';
            }
        }

        $baseInfo = [
            'logo' => 'http://www.2cpseo.com' . $logo,
            'aka' => $aka ?? '',
            'game_id' => 2,
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
