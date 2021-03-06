<?php

namespace App\Collect\hero\dota2;

use QL\QueryList;

class gamedota2
{
    protected $data_map =
        [
            "hero_name"=>['path'=>"hero_name",'default'=>''],
            "cn_name"=>['path'=>"hero_cn_name",'default'=>''],
            "en_name"=>['path'=>'hero_en_name','default'=>''],
            "aka"=>['path'=>"aka",'default'=>""],//别名
            "description"=>['path'=>"story_box",'default'=>'暂无'],
            "logo"=>['path'=>"logo",'default'=>''],
            "logo_small"=>['path'=>"logo_small",'default'=>''],
            "logo_icon"=>['path'=>"logo_icon",'default'=>''],
            "logo_rediant"=>['path'=>"logo_rediant",'default'=>''],
            "attack_type"=>['path'=>"attack_type",'default'=>''],
            "hero_type"=>['path'=>"hero_type",'default'=>''],
            "rediant"=>['path'=>"radiant",'default'=>''],
            "id"=>['path'=>"item_id",'default'=>0],
            "roles"=>['path'=>"roles",'default'=>[]],
            "stat"=>['path'=>"pro_box",'default'=>[]],
            "talent"=>['path'=>"talent_box",'default'=>[]],
            "skill"=>['path'=>"skill_box",'default'=>[]],
            "equipment"=>['path'=>"equip_box",'default'=>[]],
            ];
    public $hero_type = [
        'int'=>'智力',
        'agi'=>'敏捷',
        'str'=>'力量',
    ];
    public $role_type = [
        'Carry'=>'核心',
        'Disabler'=>'控制',
        'Initiator'=>'先手',
        'Jungler'=>'打野',
        'Support'=>'辅助',
        'Durable'=>'耐久',
        'Nuker'=>'高爆发',
        'Pusher'=>'推进',
        'Escape'=>'逃生',
        "LaneSupport"=>'对线辅助'
    ];
    public $attack_type = [
        "melee"=>"近战",
        "ranged"=>"远程"
        ];
    public function collect($arr)
    {
        $url = $arr['detail']['url'] ?? '';
        $hero_type= $arr['detail']['hero_type'] ?? '';
        $res=$this->getHeroInfo($url);
        $res['hero_type']=$hero_type;
        $cdata=[];
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

        }
        return $cdata;
    }
    public function process($arr)
    {
       /* 'hero_name'=>$hero_name,//英雄名称
            'hero_cn_name'=>$hero_name,//中文名称
            'hero_en_name'=>$hero_en_name,//英文名称
            'aka'=>$aka,//其他简称
            'logo_small'=>$logo_small,//小图片
            'logo_big'=>$logo_big,//大图片
            'logo_icon'=>$logo_icon,//icon
            'atk'=>$atk,//攻击类型
            'roles'=>$roles,//定位
            'radiant'=>$radiant,//阵营名称
            'radiant_logo'=>$radiant_logo,//别名，其他简称
            'story_box'=>$story_box,//背景故事
            'story_pic'=>$story_pic,//背景故事图片
            'talent_box'=>$talent_box,//天赋树
            'pro_box'=>$pro_box,//英雄属性
            'skill_box'=>$skill_box,//技能介绍
            'equip_box'=>$equip_box,//装备选择*/
        $arr['content']['aka'] = $arr['content']['aka']['0']??"";
        $arr['content']['logo']  = getImage($arr['content']['logo_big']);
        $arr['content']['logo_icon']  = getImage($arr['content']['logo_icon']);
        $arr['content']['logo_small']  = getImage($arr['content']['logo_small']);
        $arr['content']['logo_rediant']  = getImage($arr['content']['radiant_logo']);
        $arr['content']['logo_desc']  = getImage($arr['content']['story_pic']);
        $arr['content']['attack_type'] = array_flip($this->attack_type)[$arr['content']["atk"]];
        foreach($arr['content']['roles'] as $key => $role)
        {
            $arr['content']['roles'][$key] = array_flip($this->role_type)[$role];
        }
        foreach($arr['content']['pro_box'] as $key => $stat)
        {
            $arr['content']['pro_box'][$key]['stat_logo'] = getImage($stat['property_img']);
            unset($arr['content']['pro_box'][$key]['property_img']);
        }
        foreach($arr['content']['skill_box'] as $key => $stat)
        {
            $arr['content']['skill_box'][$key]['skill_logo'] = getImage($stat['skill_img']);
            unset($arr['content']['skill_box'][$key]['skill_img']);
        }
        $data = getDataFromMapping($this->data_map,$arr['content']);
        return $data;
    }
    public function getHeroInfo($url){
        $qt=QueryList::get($url);
        $logo_small=$qt->find(".id_div .top_hero_card img")->attr('src');
        $logo_small='https://www.dota2.com.cn'.$logo_small;
        $herotitle=$qt->find(".id_div .top_hero_card p")->text();
        $logo_big=$qt->find(".item_left .hero_info .hero_b")->attr('src');
        $logo_big='https://www.dota2.com.cn'.$logo_big;
        $logo_icon=$qt->find(".item_left .hero_info .hero_name img")->attr('src');
        $logo_icon='https://www.dota2.com.cn'.$logo_icon;
        $hero_name=$qt->find(".item_left .hero_info .hero_name ")->text();
        $hero_en_name=str_replace($hero_name,'',$herotitle);
        //攻击类型
        $atk=$qt->find(".item_left .hero_info .info_ul li:eq(0) .info_p")->text();
        //定位
        $roles=$qt->find(".item_left .hero_info .info_ul li:eq(1) .info_p")->text();
        $roles=rtrim($roles, "-");
        $roles=explode('-',$roles);
        if(count($roles)>0){
            foreach ($roles as &$val){
                $val=trim($val);
            }
        }
        $roles=$roles;
        //阵营
        $radiant=$qt->find(".item_left .hero_info .info_ul li:eq(2) .info_p")->text();//阵营名称
        $radiant_logo=$qt->find(".item_left .hero_info .info_ul li:eq(2) .info_p img")->attr('src');
        if(strpos($radiant_logo,'https') ===false){
            $radiant_logo='https:'.$radiant_logo;
        }
        //其他简称
        $other_name=$qt->find(".item_left .hero_info .info_ul li:eq(3) .info_p")->text();//阵营名称
        $other_name=explode('、',$other_name);
        $aka=$other_name;
        //英雄属性
        $pro_box=[
            [
                'property_img'=>'https://www.dota2.com.cn/images/heropedia/overviewicon_str.png',
                'property_title'=>$qt->find(".item_left .property_box .pro6_box li:eq(0) .pop_property_t")->text(),
                'property_cont'=>$qt->find(".item_left .property_box .pro6_box li:eq(0) .pop_property_cont")->html(),
            ],
            [
                'property_img'=>'https://www.dota2.com.cn/images/heropedia/overviewicon_agi.png',
                'property_title'=>$qt->find(".item_left .property_box .pro6_box li:eq(1) .pop_property_t")->text(),
                'property_cont'=>$qt->find(".item_left .property_box .pro6_box li:eq(1) .pop_property_cont")->html(),
            ],
            [
                'property_img'=>'https://www.dota2.com.cn/images/heropedia/overviewicon_int.png',
                'property_title'=>$qt->find(".item_left .property_box .pro6_box li:eq(2) .pop_property_t")->text(),
                'property_cont'=>$qt->find(".item_left .property_box .pro6_box li:eq(2) .pop_property_cont")->html(),
            ],
            [
                'property_img'=>'https://www.dota2.com.cn/event/201401/herodata/images/pro4.png',
                'property_title'=>$qt->find(".item_left .property_box .pro6_box li:eq(3) .pop_property_t")->text(),
                'property_cont'=>$qt->find(".item_left .property_box .pro6_box li:eq(3) .pop_property_cont")->html(),
            ],
            [
                'property_img'=>'https://www.dota2.com.cn/event/201401/herodata/images/pro5.png',
                'property_title'=>$qt->find(".item_left .property_box .pro6_box li:eq(4) .pop_property_t")->text(),
                'property_cont'=>$qt->find(".item_left .property_box .pro6_box li:eq(4) .pop_property_cont")->html(),
            ],
            [
                'property_img'=>'https://www.dota2.com.cn/event/201401/herodata/images/pro6.png',
                'property_title'=>'',
                'property_cont'=>'',
            ],
        ];
        if($pro_box){
            foreach ($pro_box as &$val){
                if($val['property_cont']){
                    $val['property_cont']=explode('<br>',$val['property_cont']);
                    foreach ($val['property_cont'] as &$v){
                        $v=trim($v);
                    }
                }else{
                    $val['property_cont']=[];
                }

            }
        }
        //背景故事
        $story_box=$qt->find(".item_right .story_box")->text();
        $story_pic=$qt->find(".item_right .story_box .story_pic img")->attr('src');
        $story_pic='https://www.dota2.com.cn'.$story_pic;
        //天赋树
        $talent_box_html=$qt->find('.item_right  .talent_box')->html();
        $talent_box=QueryList::html($talent_box_html)->rules(array(
            'level' => array('.level-interior','text'),//等级
            'explain' => array('.talent-explain','texts')//介绍
        ))->range('.talent_ul li')->queryData();

        //技能
        $skill_box_html=$qt->find('.item_right  .skill_box')->html();
        $skill_box= QueryList::html($skill_box_html)->rules(array(
            'skill_img' => array('.skill_wrap img','src'),
            'title' => array('.skill_wrap .skill_intro span','text'),//标题
            'skill_intro' => array('.skill_wrap .skill_intro','text'),//技能描述
            'icon_xh' => array('.skill_wrap .xiaohao_wrap .icon_xh','text'),//魔法消耗
            'icon_lq' => array('.skill_wrap .xiaohao_wrap .icon_lq','text'),//冷却时间
            'skill_bot' => array(' .skill_bot','text'),
            'skill_list' => array('.skill_ul','html')
        ))->range('#focus_dl dd')->queryData(function($item){
            $item['skill_img']='https://www.dota2.com.cn'.$item['skill_img'];
            $item['skill_intro']=trim(str_replace($item['title'],'',$item['skill_intro']));
            $skill_ul=QueryList::html($item['skill_list'])->find('li')->texts()->all();//技能属性
            $item['skill_list'] = $skill_ul;
            return $item;
        });
        //装备选择
        $equip_wrap=$qt->find(".item_right .equip_wrap")->html();
        $equip_box= QueryList::html($equip_wrap)->rules(array(
            'equip_type' => array('.equip_t','text'),
            'equip_info' => array('.equip_ul','html'),//x
        ))->range('.equip_one')->queryData(function($item){
            $item['equip_info'] = QueryList::html($item['equip_info'])->rules(array(
                'equip_imgs' => array('img','src'),//装备缩略图
                'equip_title' => array('.pop_box .equip_item_r span','text'),//标题

            ))->range('li')->queryData(function($item1){
                $en_name=str_replace(array('//www.dota2.com.cn/images/items/','_lg.png'),'',$item1['equip_imgs']);
                if(strpos($item1['equip_imgs'],'https')===false){
                    $item1['equip_imgs']='https:'.$item1['equip_imgs'];
                }
                $item1['en_name']=$en_name;

                return $item1;
            });
            return $item;
        });
        $heroInfo=[
            'hero_name'=>$hero_name,//英雄名称
            'hero_cn_name'=>$hero_name,//中文名称
            'hero_en_name'=>$hero_en_name,//英文名称
            'aka'=>$aka,//其他简称
            'logo_small'=>$logo_small,//小图片
            'logo_big'=>$logo_big,//大图片
            'logo_icon'=>$logo_icon,//icon
            'atk'=>$atk,//攻击类型
            'roles'=>$roles,//定位
            'radiant'=>$radiant,//阵营名称
            'radiant_logo'=>$radiant_logo,//别名，其他简称
            'story_box'=>$story_box,//背景故事
            'story_pic'=>$story_pic,//背景故事图片
            'talent_box'=>$talent_box,//天赋树
            'pro_box'=>$pro_box,//英雄属性
            'skill_box'=>$skill_box,//技能介绍
            'equip_box'=>$equip_box,//装备选择
        ];
        return $heroInfo;
    }
}
