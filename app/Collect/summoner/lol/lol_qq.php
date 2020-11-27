<?php

namespace App\Collect\summoner\lol;

class lol_qq
{
    protected $data_map =
        [
        ];
    public function collect($arr)
    {
        $cdata = [];
        $url = $arr['detail']['url'] ?? '';
        $res = curl_get($url);
        $res = $res['data'] ?? [];
        if (!empty($res)) {
            $res=$this->doData($res);
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

    //处理数据
    public function doData($data){
        foreach ($data as &$val){
            $val['thumb_img']='https://ossweb-img.qq.com/images/lol/img/spell/'.$val['id'].'.png';
            $val['big_img']='https://ossweb-img.qq.com/images/lol/web201310/summoner/'.$val['key'].'.jpg';
        }

        return $data;


    }
}
