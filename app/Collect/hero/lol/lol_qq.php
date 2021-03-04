<?php

namespace App\Collect\hero\lol;

use App\Libs\ClientServices;

class lol_qq
{
    protected $data_map =
        [
            "hero_name"=>['path'=>"hero.name",'default'=>''],
            "cn_name"=>['path'=>"hero.title",'default'=>''],
            "en_name"=>['path'=>"hero.alias",'default'=>''],
            "description"=>['path'=>"hero.shortBio",'default'=>'暂无'],
            "roles"=>['path'=>"hero.roles",'default'=>[]],//职业
            "logo"=>['path'=>"hero.show_list_img",'default'=>''],
            "difficulty"=>['path'=>"hero.difficulty",'default'=>0],//上手难度
            "physical_attack"=>['path'=>"hero.attack",'default'=>0],//物理攻击
            "magic_attack"=>['path'=>"hero.magic",'default'=>0],//魔法攻击
            "defense"=>['path'=>"hero.defense",'default'=>0],//防御
            "hp"=>['path'=>"hero.hp",'default'=>0],
            "mp"=>['path'=>"hero.mp",'default'=>0],
            "hp_regen"=>['path'=>"hero.hpregen",'default'=>0],//生命回复
            "mp_regen"=>['path'=>"hero.mpregen",'default'=>0],//魔法回复
            "attack_speed"=>['path'=>"hero.attackspeed",'default'=>0],//攻击速度
            "attack_range"=>['path'=>"hero.attackrange",'default'=>0],//攻击范围
            "attack_damage"=>['path'=>"hero.attackdamage",'default'=>0],//攻击
            "price"=>['path'=>"hero.goldPrice",'default'=>0],//攻击
            "move_speed"=>['path'=>"hero.movespeed",'default'=>0],//移动速度
            "magic_defense"=>['path'=>"hero.spellblock",'default'=>0],//魔法抗性
            "ally_tips"=>['path'=>"hero.allytips",'default'=>[]],//使用技巧
            "enemy_tips"=>['path'=>"hero.enemytips",'default'=>[]],//对手技巧
            "aka"=>['path'=>"hero.",'default'=>""],//别名
            "keywords"=>['path'=>"hero.keywords",'default'=>""],//关键字
            "id"=>['path'=>"hero.heroId",'default'=>[]],//原站点ID
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
        $client=new ClientServices();
        $data=$client->curlGet($url);
        $res['hero']=$data['hero'] ?? [];
        $res['skins']=$data['skins'] ?? [];
        $res['spells']=$data['spells'] ?? [];

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
        /**
         * $data['hero']=>基础信息,$data['skins']=>皮肤,$data['spells']=>皮肤,
         * hero: $res['show_list_img'] = 'https://game.gtimg.cn/images/lol/act/img/champion/' . $res['alias'] . '.png';
         */
        foreach($arr['content']['hero']['roles'] as $key => $value)
        {
            $arr['content']['hero']['roles'][$key] = $this->role_list[$value]??"未知";
        }
        ksort($arr['content']['hero']);
        $arr['content']['hero']['show_list_img'] = getImage('https://game.gtimg.cn/images/lol/act/img/champion/' . $arr['content']['hero']['alias'] . '.png');
        $data = getDataFromMapping($this->data_map,$arr['content']);
        return $data;
    }
    public function processSkins($arr)
    {
        $skinList = [];
        if(isset($arr['content']['skins']))
        {
            foreach($arr['content']['skins'] as $key => $value)
            {
                if($value['chromas']==0 && $value['chromas'] !=""){
                    foreach($value as $k => $v)
                    {
                        if(substr($k,-3)=="Img")
                        {
                            $value[$k] = getImage($v);
                        }
                    }
                    $skinList[$value['skinId']] = ["skin_id"=>$value['skinId'],
                        'hero_id'=>$value['heroId'],
                        'data'=>$value];
                }

            }
        }
        return $skinList;
    }
    public function processSpells($arr)
    {
        $spellList = [];
        if(isset($arr['content']['spells']))
        {
            foreach($arr['content']['spells'] as $key => $value)
            {
                $value['abilityIconPath'] = getImage($value['abilityIconPath']);
                $spellList[$value['spellKey']] = [
                    'spell_name'=>$value['name'],
                    'hero_id'=>$value['heroId'],
                    'data'=>$value];
            }
        }
        return $spellList;
    }
}
