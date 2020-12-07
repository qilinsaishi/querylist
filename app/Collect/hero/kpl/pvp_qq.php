<?php

namespace App\Collect\hero\kpl;

use App\Libs\ClientServices;
use QL\QueryList;

class pvp_qq
{
    protected $data_map =
        [
        ];
    public function collect($arr)
    {
        $res=[];
        $url = $arr['detail']['url'] ?? '';
        //$res = $this->getData($url);//curl获取json数据
        $res['cname']=$arr['detail']['cname'] ?? '';
        $res['title']=$arr['detail']['title'] ?? '';
        $res['hero_type"']=$arr['detail']['hero_type"'] ?? '';
        $res['hero_type2"']=$arr['detail']['hero_type2"'] ?? '';
        $res['logo"']=$arr['detail']['logo"'] ?? '';
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
        /*var typeMap = {
                    3: '坦克',
                    1: '战士',
                    2: '法师',
                    4: '刺客',
                    5: '射手',
                    6: '辅助',
                    10: '限免',
                    11: '新手'
                }*/

        var_dump($arr);
    }

    public function getData($url){

        $data=[];

        return $data;
    }
}
