<?php

namespace App\Collect\equipment\lol;

class lol_qq
{
    protected $data_map =
        [
            "equipment_name" => ['path' => "name", 'default' => ''],
            "description" => ['path' => "description", 'default' => '暂无'],
            "logo" => ['path' => "iconPath", 'default' => ''],
            "maps" => ['path' => "maps", 'default' => []],//出现地图
            "cn_name" => ['path' => "name", 'default' => ''],//中文名
            "en_name" => ['path' => "", 'default' => ''],//英文名
            "price" => ['path' => "price", 'default' => 0],//价格
            "from_list" => ['path' => "from_list", 'default' => []],//合成需要的道具列表
            "into_list" => ['path' => "into", 'default' => []],//可以合成的道具列表
            "aka" => ['path' => "", 'default' => ""],//别名
        ];
    protected $item_tree =
        [
            "consumable" => '消耗品',
            "defense" => '防御类',
            "health" => '生命值',
            "armor" => '护甲',
            "spellblock" => '魔法抗性',
            "healthregen" => '生命回复',
            "tenacity" => '韧性',
            "attack" => '攻击类',
            "damage" => '攻击力',
            "criticalstrike" => '暴击',
            "attackspeed" => '攻击速度',
            "lifesteal" => '生命偷取',
            "magic" => '法术类',
            "spelldamage" => '法术强度',
            "cooldownreduction" => '冷却缩减',
            "spellvamp" => '法术吸血',
            "mana" => '法力值',
            "manaregen" => '法力回复',
            "movement" => '移动速度',
            "boots" => '鞋子',
            "nonbootsmovement" => '其他移动速度物品'
        ];
    protected $stats = [
        "移动速度" => 'NonbootsMovement',
        "基础法力回复" => 'ManaRegen',
        "基础生命回复" => 'HealthRegen',
        "生命值" => 'Health',
        "暴击几率" => 'CriticalStrike',
        "法术强度" => 'SpellDamage',
        "法力" => 'Mana',
        "护甲" => 'Armor',
        "魔法抗性" => 'SpellBlock',
        "攻击力" => 'Damage',
        "穿甲" => 'ArmorPenetration',
        "护甲穿透" => 'ArmorPenetration',
        "技能急速" => 'CooldownReduction',
        "金币/10秒" => 'GoldPer',
        "生命偷取" => 'LifeSteal',
        "全能吸血" => 'SpellVamp',
        "攻击速度" => 'AttackSpeed',
        "治疗和护盾强度" => 'OnHit',
        "法术穿透" => 'MagicPenetration'
    ];

    public function collect($arr)
    {
        $cdata = [];
        $url = $arr['detail']['url'] ?? '';
        $res = curl_get($url);
        $res = $res['items'] ?? [];
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
        $data = [];
        $arr['content'] = array_combine(array_column($arr['content'], "itemId"), array_values($arr['content']));
        ksort($arr['content']);
        $trees = (array_combine(array_keys($arr['content']), array_column($arr['content'], "from")));
        foreach ($trees as $key => $value) {
            if (is_array($value)) {
                $t = [];
                foreach ($value as $k => $v) {
                    if (!isset($t[$v])) {
                        $t[$v] = ["count" => 1];
                    } else {
                        $t[$v]["count"]++;
                    }
                    $trees[$key] = $t;
                }
            }
        }
        foreach ($trees as $key => $value) {
            if (is_array($value)) {
                $trees[$key] = $this->getSons($trees, $key);
            }
        }
        foreach ($arr['content'] as $key => $value) {
            if (strpos($value['name'], 'rarityLegendary')) {
                $arr['content'][$key]['name'] = $this->getInnerText('rarityLegendary', $value['name']);
            }
            $arr['content'][$key]['stats'] = $this->getStats($value['description']);
            $arr['content'][$key]['from_list'] = $trees[$key] ?? [];
            $arr['content'][$key]['iconPath'] = getImage($arr['content'][$key]['iconPath']);
            $data[$key] = getDataFromMapping($this->data_map, $arr['content'][$key]);
        }
        return $data;
    }

    //获取子级数据
    public function getSons($arr, $i)
    {
        $return = $arr[$i];
        foreach ($return as $key => $value) {
            if (is_array($arr[$key])) {
                $return[$key]['item'] = $this->getSons($arr, $key);
            }
        }
        return $return;
    }

    //从描述的<stats>标签中获取属性数据
    public function getStats($text = "")
    {
        $data = $this->getInnerText('stats', $text);
        $stat = [];
        $t = explode('<br>', $data);
        foreach ($t as $key => $value) {
            $needle = '</attention>';
            $start = strpos($value, $needle);
            if ($start > 0) {
                $start = $start + strlen($needle);
                $name = substr($value, $start);
                $v = $this->getInnerText('attention', substr($value, 0, $start));
                $stat[$this->stats[$name] ?? $name] = $v;
            }
        }
        return $stat;
    }

    public function getInnerText($name, $text)
    {
        $pattern = '{<' . $name . '>(.*?)</' . $name . '>}';
        preg_match($pattern, $text, $return);
        return $return[1] ?? "";
    }

}
