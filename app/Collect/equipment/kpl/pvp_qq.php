<?php

namespace App\Collect\equipment\kpl;

use App\Libs\ClientServices;

class pvp_qq
{
    protected $data_map =
        [
            1=>[
                "equipment_name" => ['path' => "item_name", 'default' => ''],
                "description" => ['path' => "description", 'default' => '暂无'],
                "cn_name" => ['path' => "item_name", 'default' => ''],//中文名
                "en_name" => ['path' => "", 'default' => ''],//英文名
                "type" => ['path' => "", 'default' => 1],
                "logo" => ['path' => "logo", 'default' => ""],
                "price" => ['path' => "price", 'default' => 0],//价格
                "aka" => ['path' => "", 'default' => ""],//别名
                "id" => ['path' => "item_id", 'default' => 0],//对应站点ID
                "sub_type" => ['path' => "item_type", 'default' => 1],
                "level" => ['path' => "", 'default' => 0],
                ],
            2=>[
                "equipment_name" => ['path' => "itemname", 'default' => ''],
                "description" => ['path' => "description", 'default' => '暂无'],
                "cn_name" => ['path' => "itemname", 'default' => ''],//中文名
                "en_name" => ['path' => "", 'default' => ''],//英文名
                "type" => ['path' => "", 'default' => 2],
                "logo" => ['path' => "logo", 'default' => ""],
                "price" => ['path' => "", 'default' => 0],//价格
                "aka" => ['path' => "", 'default' => ""],//别名
                "id" => ['path' => "itemid", 'default' => 0],//对应站点ID
                "sub_type" => ['path' => "itemtype", 'default' => 1],
                "level" => ['path' => "itemlv", 'default' => 0],
            ],
        ];
    protected $sub_type_list =
        [
            1=>[
                1=>'攻击',2=>'法术',3=>'防御',4=>'移动',5=>'打野',6=>'游走'
                ],

            2=>[
                1=>'装备',2=>'道具',3=>'额外技能'
            ],
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
         * 以上字段名都需要截取头部有实际意义的部分
         * 装备logo：例如https://game.gtimg.cn/images/yxzj/img201606/itemimg/2003.jpg（2003表示装备id）
         */
        $data = [];
        if($arr['content']['type']==1)
        {
            foreach($arr['content']['items']  as $key => $value)
            {
                $value['description'] = $value['des1']."。".($value['des2']??"");
                $value['description'] = preg_replace("/<([a-zA-Z]+)[^>]*>/", "",$value['description']);
                $value['description'] = preg_replace("{</([a-zA-Z]+)[^>]*>}", "",$value['description']);
                $value['logo'] = "https://game.gtimg.cn/images/yxzj/img201606/itemimg/".$value['item_id'].".jpg";
                $data[$key] = getDataFromMapping($this->data_map[1], $value);
            }
        }
        elseif($arr['content']['type']==2)
        {
            foreach($arr['content'] as $key => $value)
            {
                if(substr($key,0,4)=="bjtw")
                {
                    $data_key = $key;
                }
            }
            foreach($arr['content'][$data_key]  as $key => $value)
            {
                foreach($value as $k2 => $v2)
                {
                    if(substr($k2,0,8)=="itemname")
                    {
                        $value['itemname'] = $v2;
                        unset($value[$k2]);
                    }
                    elseif(substr($k2,0,6)=="itemid")
                    {
                        $value['itemid'] = $v2;
                        unset($value[$k2]);
                    }
                    elseif(substr($k2,0,6)=="itemlv")
                    {
                        $value['itemlv'] = str_replace("级","",$v2);
                        unset($value[$k2]);
                    }
                    elseif(substr($k2,0,4)=="des1")
                    {
                        $value['des1'] = $v2;
                        unset($value[$k2]);
                    }
                    elseif(substr($k2,0,4)=="des2")
                    {
                        $value['des2'] = $v2;
                        unset($value[$k2]);
                    }
                    elseif(substr($k2,0,8)=="itemtype")
                    {
                        $value['itemtype'] = $v2;
                        unset($value[$k2]);
                    }
                }
                $value['description'] = $value['des1']."。".($value['des2']??"");
                $value['description'] = preg_replace("/<([a-zA-Z]+)[^>]*>/", "",$value['description']);
                $value['description'] = preg_replace("{</([a-zA-Z]+)[^>]*>}", "",$value['description']);
                $value['logo'] = "https://game.gtimg.cn/images/yxzj/img201606/itemimg/".$value['itemid'].".jpg";
                $data[$key] = getDataFromMapping($this->data_map[2], $value);
            }
        }
        return $data;
    }

}
