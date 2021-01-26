<?php

namespace App\Collect\event\kpl;

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
        $res = $this->getEventData($url);


        if (!empty($res)) {

            $cdata = [
                'mission_id' => $arr['mission_id'],//任务id
                'content' => is_array($res) ? json_encode($res) : [],
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
    public function getEventData($url)
    {
        $ql = QueryList::get($url);
        $logo = $ql->find('.match-header-infos img')->attr('src');
        $logo = 'http://www.2cpseo.com' . $logo;
        $match_name = $ql->find('.match-name')->text();
        $match_time = $ql->find('.match-time')->text();
        $startTime='';
        $endTime='';
        if (strpos($match_time, '比赛时间：') !== false) {
            $match_time = str_replace('比赛时间：', '', $match_time);
            if(strpos($match_time, ' - ') !== false){
                $match_temp_time=  explode(' - ', $match_time);
                $startTime=$match_temp_time[0] ?? 0;
                $endTime =$match_temp_time[1] ?? 0;
            }
        }

        $baseInfo = [
            'logo' => $logo,
            'title' => $match_name ?? '',
            'start_time' => $startTime ?? '',
            'end_time' => $endTime ?? '',
            'game' =>'kpl',//game: 1表示lol
        ];

        $tapType = $ql->find('.game-tabs-item')->texts()->all();
        $pkTeam = [];
        if (!empty($tapType)) {
            foreach ($tapType as $key => &$val) {
                $pkTeam[$key]['type'] = $val;
                $game_list_html = $ql->find('.game-list:eq('.$key.')')->html();
                $pkTeam[$key]['teamInfo'] = QueryList::html($game_list_html)->rules(array(
                    'game_date' => array('.game-date','text'),
                    'game_list' => array('.game-items','html')
                ))->range('.game-list-item')->queryData(function($item){
                    // 注意这里的QueryList对象与上面的QueryList对象是同一个对象
                    $item['game_items'] = QueryList::html($item['game_list'])->rules(array(
                        'date_no' => array('.game-time','text'),
                        'game-name' => array('.game-name','text'),
                        'team_name' => array('.team-name','texts'),

                    ))->range('a')->queryData();
                    unset($item['game_list']);
                    return $item;
                });
            }
        }
        $res['baseInfo'] = $baseInfo ?? [];//赛事基本
        $res['pkTeam'] = $pkTeam ?? [];//pk战队
        return $res;
    }
}
