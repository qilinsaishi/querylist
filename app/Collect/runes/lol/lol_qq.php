<?php

namespace App\Collect\runes\lol;

class lol_qq
{
    /**
     * 符文
     */
    protected $data_map =
        [
            "rune_name"=>['path'=>"name",'default'=>''],
            "cn_name"=>['path'=>"name",'default'=>''],
            "en_name"=>['path'=>"",'default'=>''],
            "description"=>['path'=>"description",'default'=>'暂无'],
            "aka"=>['path'=>"slogan",'default'=>''],
            "bonuses"=>['path'=>"bonuses",'default'=>[]],
            "slots"=>['path'=>"slots",'default'=>[]],

        ];
    public function collect($arr)
    {
        $cdata = [];
        $url = $arr['detail']['url'] ?? '';
        $res = curl_get($url);
        $res = $res['styles'] ?? [];
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

        $data = ['rune'=>[],'runeDetail'=>[]];
        $rune_detail_list = [];
        foreach($arr['content']  as $key => $value)
        {
            foreach($value['bonuses'] as $k => $v)
            {
                $v['value'] = preg_replace("/<([a-zA-Z]+)[^>]*>/","",$v['value']);
                $arr['content'][$key]['bonuses'][$k]['value'] = preg_replace("{</([a-zA-Z]+)[^>]*>}","",$v['value']);
            }
            foreach($value['slots'] as $k => $v)
            {
                foreach($v['runes'] as $k2 => $v2)
                {
                    $v2['longDescription'] = preg_replace("/<([a-zA-Z]+)[^>]*>/","",$v2['longDescription']);
                    $v2['longDescription'] = preg_replace("{</([a-zA-Z]+)[^>]*>}","",$v2['longDescription']);
                    $v2['shortDescription'] = preg_replace("/<([a-zA-Z]+)[^>]*>/","",$v2['shortDescription']);
                    $v2['shortDescription'] = preg_replace("{</([a-zA-Z]+)[^>]*>}","",$v2['shortDescription']);
                    $arr['content'][$key]['slots'][$k]['runes'][$k2] = $v2['runeId'];
                    $data['runeDetail'][$v2['runeId']] = ["rune_name"=>$v2['name'],
                        'shortDescription'=>$v2['shortDescription'],
                        'longDescription'=>$v2['longDescription'],
                        'logo'=>'https://lol.qq.com/act/a20170926preseason/img/runeBuilder/runes/108x108/'.$v2['runeId'].'.png',
                        'rune_id'=>$v2['runeId']
                        ];
                }
            }
        }
        foreach($arr['content']  as $key => $value)
        {
            $data['rune'][$key] = getDataFromMapping($this->data_map,$arr['content'][$key]);
        }
        return $data;
    }
}
