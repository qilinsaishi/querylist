<?php

namespace App\Collect\information\lol;

use App\Libs\ClientServices;
use QL\QueryList;

class kuai8
{
    //资讯攻略
    protected $data_map =
        [
        ];
    public function collect($arr)
    {
        $cdata = [];
        $url = $arr['detail']['url'] ?? '';
        $detail_ql=QueryList::get($url);
        $content=$detail_ql->find('.article-detail .a-detail-cont')->html();
        $title=$arr['detail']['title'] ?? '';
        $desc=$arr['detail']['desc'] ?? '';
        $img_url=$arr['detail']['img_url'] ?? '';
        $dtime=$arr['detail']['dtime'] ?? '';
        $res=[
            'title'=>$title,
            'desc'=>$desc,
            'dtime'=>$dtime,
            'source_url'=>$url,
            'img_url'=>$img_url,
            'content'=>$content ?? '',
        ];
        if (!empty($res)) {

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

            return $cdata;
        }
    }
    public function process($arr)
    {
        var_dump($arr);
    }
}
