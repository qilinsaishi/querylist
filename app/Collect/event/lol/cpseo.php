<?php

namespace App\Collect\event\lol;

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
        var_dump($arr);
    }

    public function getEventData($url){
        $ql = QueryList::get($url);
        $logo=$ql->find('.kf_roster_dec img')->attr('src');
        $logo='http://www.2cpseo.com'.$logo;
        $wraps=$ql->find('.text_wrap:eq(0) .text_2 p')->text();
        $wraps=explode("\n",$wraps);
        if($wraps){
            foreach ($wraps as $key=>$val){
                if(strpos($val,'英雄联盟：') !==false) {
                    $title=str_replace('英雄联盟：','',$val);
                }
                if(strpos($val,'开始时间：') !==false) {
                    $startTime=str_replace('开始时间：','',$val);

                }
                if(strpos($val,'结束时间：') !==false) {
                    $endTime=str_replace('结束时间：','',$val);
                }

            }
        }

        $baseInfo=[
            'logo'=>$logo,
            'title'=>$title ?? '',
            'start_time'=>$startTime ?? '',
            'end_time'=>$endTime ?? '',
            'game_id'=>1,//game: 1表示lol
        ];

        $tapType=$ql->find('.tranding_tab .nav-tabs li')->texts()->all();
        $pkTeam=[];
        if(!empty($tapType)){
            foreach ($tapType as $key=>&$val){
                $pkTeam[$key]['type']=$val;
                $pkTeam[$key]['teamInfo'] = $ql->rules([
                    'date_2' => ['.date_2', 'text'],
                    'opponents_dec' => ['.kf_opponents_dec  h6', 'texts'],
                    'dtime' => ['.kf_opponents_gols  p', 'text']
                ])->range('#home'.($key+1).' li')->queryData();
            }
        }
        $res['baseInfo']=$baseInfo ?? [];//赛事基本
        $res['pkTeam']=$pkTeam ?? [];//pk战队
        return $res;
    }
}
