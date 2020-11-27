<?php

namespace App\Collect\hero\lol;

class lol_qq
{
    protected $data_map =
        [
            "hero_name"=>['path'=>"name",'default'=>''],
            "cn_name"=>['path'=>"title",'default'=>''],
            "en_name"=>['path'=>"alias",'default'=>''],
            "description"=>['path'=>"shortBio",'default'=>'暂无'],
            "roles"=>['path'=>"roles",'default'=>[]],//职业
            "logo"=>['path'=>"show_list_img",'default'=>''],
            "difficulty"=>['path'=>"difficulty",'default'=>0],//上手难度
            "physical_attack"=>['path'=>"attack",'default'=>0],//物理攻击
            "magic_attack"=>['path'=>"magic",'default'=>0],//魔法攻击
            "defense"=>['path'=>"defense",'default'=>0],//防御
            "hp"=>['path'=>"hp",'default'=>0],
            "mp"=>['path'=>"mp",'default'=>0],
            "hp_regen"=>['path'=>"hpregen",'default'=>0],//生命回复
            "mp_regen"=>['path'=>"mpregen",'default'=>0],//魔法回复
            "attack_speed"=>['path'=>"attackspeed",'default'=>0],//攻击速度
            "attack_range"=>['path'=>"attackrange",'default'=>0],//攻击范围
            "attack_damage"=>['path'=>"attackdamage",'default'=>0],//攻击
            "price"=>['path'=>"goldPrice",'default'=>0],//攻击
            "move_speed"=>['path'=>"movespeed",'default'=>0],//移动速度
            "magic_defense"=>['path'=>"spellblock",'default'=>0],//魔法抗性
            "ally_tips"=>['path'=>"allytips",'default'=>[]],//使用技巧
            "enemy_tips"=>['path'=>"enemytips",'default'=>[]],//对手技巧
            "aka"=>['path'=>"",'default'=>""],//别名
        ];
    //职业列表
    protected $role_list =
        [
             "fighter"=>"战士",
             "mage"=>"法师",
             "assassin"=>"刺客",
             "tank"=>"坦克",
             "marksman"=>"射手",
             "support"=>"辅助"
        ];
    //lol 英雄数据接口
    public function collect($arr)
    {
        $cdata = [];
        $url = $arr['detail']['url'] ?? '';
        $res = curl_get($url);
        $res = $res['hero'] ?? [];
        if (!empty($res)) {
            $res['show_list_img'] = 'https://game.gtimg.cn/images/lol/act/img/champion/' . $res['alias'] . '.png';
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
        $className = 'App\Libs\CollectLib';
        $lib = new $className;
        foreach($arr['content']['roles'] as $key => $value)
        {
            $arr['content']['roles'][$key] = $this->role_list[$value]??"未知";
        }
        echo "speed:".$arr['content']['attackspeed']."\n";

        ksort($arr['content']);
        $data = $lib->getDataFromMapping($this->data_map,$arr['content']);
        return $data;
    }
}
