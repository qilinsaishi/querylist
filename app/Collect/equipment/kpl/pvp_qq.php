<?php

namespace App\Collect\equipment\kpl;

use App\Libs\ClientServices;

class pvp_qq
{
    protected $data_map =
        [
        ];

    public function collect($arr)
    {
        $url = $arr['detail']['url'] ?? '';
        $type = $arr['detail']['type'] ?? '';
        $client = new ClientServices();
        $res = $client->curlGet($url);
        $cdata = [];
        if ($type == 1) {//常规模式
            $cdata['items'] = $res;
            $cdata['type'] = $type;
        } else {//$type=2边境突围模式
            $cdata['type'] = $type;
            $cdata['bjtwzbsy_ba'] = $res['bjtwzbsy_ba'];
        }
        if (!empty($cdata)) {
            $cdata = [
                'mission_id' => $arr['mission_id'],
                'content' => json_encode($cdata),
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
        /**
         * $type=1时，表示常规模式，$type=2表示边境突围模式
         * $type=1{
         * item_id=>装备id,item_name=>装备名称，item_type=装备类型（1=>表示攻击，2=>法术,3=>防御,4=>移动,5=>打野,6=>游走）
         *  price=>销售价，total_price=>总价,des1=>属性1，des2=>属性2
         * }
         * $type=2{
         * itemnamezwm_cd=>名称，itemidzbid_4a=>装备id,itemlvzbdj_96=>等级，itemtypezbfl_30装备分类（1=>装备,2=>道具,3=>额外技能）
         * des1zbsx_a6=>属性1，属性2
         * }
         * 装备logo：例如https://game.gtimg.cn/images/yxzj/img201606/itemimg/2003.jpg（2003表示装备id）
         */

        var_dump($arr);
    }

}
