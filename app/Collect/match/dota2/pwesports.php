<?php

namespace App\Collect\match\dota2;

class pwesports
{
    protected $data_map =
        [
        ];
    public function collect($arr)
    {
        $cdata = [];
        $res = $url = $arr['detail'] ?? [];
        if (!empty($res)) {
            //处理战队采集数据
            $cdata = [
                'mission_id' => $arr['mission_id'],
                'content' => json_encode($res),
                'game' => $arr['game'],
                'source_link' => '',
                'title' => $arr['title'] ?? '',
                'mission_type' => $arr['mission_type'],
                'source' => $arr['source'],
                'status' => 1,
            ];
            //处理战队采集数据

        }

        return $cdata;
    }
    public function process($arr)
    { /**
     * {
     * "id":13851,
     * "team1":{
     * "id":5911,
     * "name":"Dalanjing Gaming",
     * "abbrev":"DLG", //
     * "app_team_name":"DLG",////不一定存在
     * "logo":"https://img.dota2.com.cn/maps/f6/ba/f6ba66bea421856ea0301a6e91950f4d1607421695.png",
     * "logo_backup":"https://img.dota2.com.cn/maps/f6/ba/f6ba66bea421856ea0301a6e91950f4d1607421695.png",
     * "score":"0",//积分
     * "win_prize_num":"104,000",//不一定存在
     * "match_phase_title":"亚军"//不一定存在
     * },
     * "team2":{
     * "id":5931,
     * "name":"Ink Ice",
     * "abbrev":"Ink Ice",
     * "app_team_name":"INK ICE",
     * "logo":"https://img.dota2.com.cn/maps/a9/67/a967fd5e901e3d52f24abe89834840571607504965.png",
     * "logo_backup":"https://img.dota2.com.cn/maps/a9/67/a967fd5e901e3d52f24abe89834840571607504965.png",
     * "score":"2",
     * "win_prize_num":"157,600",
     * "match_phase_title":"冠军"
     * },
     * "win_team_id":5931,
     * "date":"2020-12-20",
     * "time":"19:00",
     * "timestamp":1608462000,
     * "link":[
     *
     * ],
     * "game_status":2,
     * "phase":"major",
     * "stage":"main",
     * "match_id_list":[
     * 5750114030,
     * 5750204863
     * ],
     * "type":"pwl",
     * "season":"3",
     * "game":"dota2",
     * "source":"pwesports"
     * }
     */
        var_dump($arr);
    }
}
