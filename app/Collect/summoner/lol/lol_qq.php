<?php

namespace App\Collect\summoner\lol;

class lol_qq
{
    /*
     * 召唤师技能
     */
    protected $data_map =
        [
            "skill_name"=>['path'=>"name",'default'=>''],
            "cn_name"=>['path'=>"name",'default'=>''],
            "en_name"=>['path'=>"id",'default'=>''],
            "aka"=>['path'=>"",'default'=>""],//别名
            "description"=>['path'=>"description",'default'=>'暂无'],
            "logo"=>['path'=>"big_img",'default'=>''],
            "rank"=>['path'=>"maxrank",'default'=>''],
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
        $data = [];
        foreach($arr['content'] as $key => $value)
        {
            $value["id"] = str_replace("Summoner","",$value["id"]);
            $value['big_img'] = getImage($value['big_img']);
            $data[$key] = getDataFromMapping($this->data_map,$value);
        }
        return $data;
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
