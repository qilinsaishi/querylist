<?php

namespace App\Collect\equipment\dota2;

class gamedota2
{
    protected $data_map =
        [
        ];

    public function collect($arr)
    {
        $cdata = [];
        $res = $arr['detail'] ?? [];
        $level=$this->getLevelData('mysterious_hat');
        $res['level']=$level ?? 0;
        if (count($res)>0) {print_r($res);exit;
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

        }
        return $cdata;
    }

    public function process($arr)
    {
        /**
         * id=>131 官网装备id
         * img //图片
         * en_name//英文名称
         * dname //名称
         * qual
         * cost //价格
         * desc //描述
         * notes //说明
         * attrib //属性
         * mc   //魔法消耗
         * cd //冷却时间
         * lore //传说
         * level//等级（大于0表示中立物品）
         * requirements=>Array  //合成配件：有值表示合成
         * (
         * [0] => ring_of_health
         * [1] => cloak
         * [2] => ring_of_regen
         * )
         *
         *
         */


        var_dump($arr);
    }
    //中立物品等级
    public function getLevelData($en_name){
        $levelData=[];
        $neutralitems='https://www.dota2.com.cn/neutralitems/json';
        $itemData=curl_get($neutralitems);
        foreach ($itemData as $k=>$v){
            foreach ($v as $v1){
                $levelData[$v1]=str_replace('level_','',$k);
            }
        }
        $level=0;
        if(isset($levelData[$en_name])){
            $level=$levelData[$en_name];
        }

        return $level;
    }
}
