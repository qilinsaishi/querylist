<?php

namespace App\Collect\player\kpl;

use App\Libs\AjaxRequest;
use QL\QueryList;

class wanplus
{
    protected $data_map =
        [
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
        var_dump($arr);
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
