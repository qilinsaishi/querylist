<?php

namespace App\Collect\team\kpl;

use App\Libs\AjaxRequest;
use QL\QueryList;

class wanplus
{
    protected $data_map =
        [
        ];
    public function collect($arr)
    {
        $return = [];
        $url = $arr['detail']['url'] ?? '';
        $title = $arr['detail']['title'] ?? '';
        $ajaxRequest=new AjaxRequest();
        $res = $ajaxRequest->getCollectWanplusTeam($url);
        if($res['title']=='') {
            $res['title']=$title;
        }
        $cdata = [];
        if (!empty($res))
        {
            //处理战队采集数据
            $cdata = [
                'mission_id' => $arr['mission_id'],
                'content' => json_encode($res),
                'game' => $arr['game'],
                'source_link'=>$url,
                'title'=>$arr['detail']['title'] ?? '',
                'mission_type'=>$arr['mission_type'],
                'source'=>$arr['source'],
                'status' => 1,
            ];
            //处理战队采集数据
            $return = $cdata;
        }
        return $return;

    }
    public function process($arr)
    {
        var_dump($arr);
    }

}
