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
        $res = [];
        $url = $arr['detail']['url'] ?? '';
        $itemId = $arr['detail']['ename'] ?? '';
        $res = $this->getData($url, $itemId);//curl获取json数据
        $res['cname'] = $arr['detail']['cname'] ?? '';
        $res['title'] = $arr['detail']['title'] ?? '';
        //一个英雄可以对应多个选择类型
        $res['hero_type'] = $arr['detail']['hero_type'] ?? '';
        $res['hero_type2'] = $arr['detail']['hero_type2'] ?? '';
        $res['logo"'] = $arr['detail']['logo'] ?? '';
        $res['item_id"'] = $itemId;
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
         * 对应数组hero_type，hero_type2
         * var typeMap = {3: '坦克',1: '战士',2: '法师',4: '刺客',5: '射手',6: '辅助',10: '限免',11: '新手'}
         * 'skinData';//皮肤
         * 'scoreInfo'//评分
         * 'heroStory'//背景故事
         * 'history']=$history; //英雄介绍
         * 'skillBaseInfo'//技能介绍
         * 'suggList'//铭文搭配建议
         * 'addSkills'//技能加点建议
         * 'heroInfoBox'//英雄关系
         * 'equipBox'//出装建议
         * "cname" => "廉颇"
         * "title" => "正义爆轰"
         * "hero_type" => 3
         * "hero_type2" => ""*/


        var_dump($arr);
    }

    public function getData($url, $itemId)
    {

        $html = iconv('gb2312', 'utf-8', file_get_contents($url));
        $ql = QueryList::html($html);

        //皮肤
        $skinImg = $ql->find('.pic-pf-list3 ')->attr('data-imgname');
        $skiArr = explode('|', $skinImg);
        $tempSkiArr = [];
        $skinData = [];
        if ($skiArr) {
            foreach ($skiArr as $key => &$val) {
                $tempSkiArr = explode('&', $val);
                $smallImg = 'https://game.gtimg.cn/images/yxzj/img201606/heroimg/' . $itemId . '/' . $itemId . '-smallskin-' . ($key + 1) . '.jpg';
                $bigImg = 'https://game.gtimg.cn/images/yxzj/img201606/heroimg/' . $itemId . '/' . $itemId . '-bigskin-' . ($key + 1) . '.jpg';
                $skinData[$key]['smallImg'] = $smallImg;//小图
                $skinData[$key]['bigImg'] = $bigImg;//大图
                $skinData[$key]['name'] = $tempSkiArr[0] ?? '';//皮肤名称
            }
        }
        //评分能力
        $baseText = $ql->find('.cover-list-text')->texts()->all();
        $baseBars = $ql->find('.cover-list-bar .ibar')->attrs('style');
        $scoreInfo = [];
        if ($baseText) {
            foreach ($baseText as $key => $val) {
                $scoreInfo[$key]['name'] = $val;
                $baseBar = str_replace(['width:', '%'], '', $baseBars[$key]);
                $scoreInfo[$key]['value'] = $baseBar ?? 0;
            }
        }
        //背景故事
        $heroStory = $ql->find('#hero-story .pop-bd')->html();
        //英雄介绍
        $history = $ql->find('#history .pop-bd')->html();

        //技能介绍
        $baseText = $ql->find('.cover-list-text')->texts()->all();
        $skillInfo = [];
        $skillImg = $ql->find('.skill-u1 img')->attrs('src')->all();//dd($skillImg);
        //http://game.gtimg.cn/images/yxzj/img201606/heroimg/105/10500.png
        //game.gtimg.cn/images/yxzj/img201606/heroimg/105/10500.png
        $skillName = $ql->find('.skill-show .show-list .skill-name')->texts()->all();
        $skillDesc = $ql->find('.skill-show .show-list .skill-desc')->htmls()->all();
        //第五个技能时
        $skillNo5Name = $ql->find('.skill-show .show-list:eq(4) .skill-name')->text();
        $skillNo5Desc= $ql->find('.skill-show .show-list:eq(4) .skill-desc')->text();
        if($skillNo5Name !=''){//超过五张图片特殊处理
            $skillNo5=$ql->find('.no5')->attr('data-img');
            array_push($skillImg,$skillNo5);
            array_push($skillName,$skillNo5Name);
            array_push($skillDesc,$skillNo5Desc);
        }

        $skillBaseInfo=[];
        if($skillImg){
            foreach ($skillImg as $key=>$val){
                if($val!='###'){
                    $skillBaseInfo[$key]['killImg']='http:'.$val;//技能图片
                    if($skillName[$key]) {
                        $names=explode('冷却值',$skillName[$key]);
                        $skillBaseInfo[$key]['name']=$names[0] ??'';
                        $times=explode('消耗',$names[1]);
                        $skillBaseInfo[$key]['cooling']='冷却值'.$times[0] ??'';
                        $skillBaseInfo[$key]['consume']='消耗'.$times[1] ??'';
                    }
                    $skillBaseInfo[$key]['skillDesc']=$skillDesc[$key];
                }
            }
        }
        $skillBaseInfo=array_values($skillBaseInfo);
        //铭文搭配建议
        //铭文id,这个关联必须先执行inscription 这个铭文脚本，而且必须保证ming_id 与下面的保存一致
        $suggListIds = $ql->find('.sugg-u1')->attr('data-ming');
        $suggTips = $ql->find('.sugg-tips')->text();//铭文描述
        $suggList = [
            'sugglistIds' => $suggListIds,
            'suggTips' => $suggTips,
        ];


        //技能加点建议
        $suggInfo2Names = $ql->find('.sugg-info2 .sugg-name')->htmls()->all();//名称
        $suggInfo2Imgs = $ql->find('.sugg-info2 .sugg-skill img')->attrs('src')->all();//名称
        $addSkills = [];
        $tempSuggInfos = [];
        if ($suggInfo2Imgs) {
            foreach ($suggInfo2Imgs as $key => $val) {
                $addSkills[$key]['killImg'] = 'http:' . $val;//技能图片;
                $tempSuggInfos = explode('</b><span>', $suggInfo2Names[$key]);
                $addSkills[$key]['name'] = str_replace('<b>', '', $tempSuggInfos[0]);
                foreach ($skillBaseInfo as $v) {
                    if ($v['killImg'] == 'http:' . $val) {
                        $addSkills[$key]['desc'] = $v['name'];
                    }
                }
            }
        }
        //召唤师技能
        $summonerSkill = [];
        $summonerSkillName = $ql->find('.sugg-info2 .sugg-name3 b')->text();//大标题
        $summonerSkillDesc = $ql->find('.sugg-info2 .sugg-name3 span')->text();//名称
        $summonerSkillId = $ql->find('.sugg-info2 #skill3')->attr('data-skill');//关联召唤师技能id(80115|80121)
        $summonerSkill = [
            'summonerSkillName' => $summonerSkillName ?? '',
            'summonerSkillDesc' => $summonerSkillDesc ?? '',
            'summonerSkillId' => $summonerSkillId ?? '',
        ];
        //英雄关系:0:最佳搭档 1:压制英雄 2:被压制英雄 (英雄原有id 需要保存一个字段)
        $heroInfoBox = [];
        $heroHdTitle = $ql->find('.hero-info-box .hero-hd li')->texts()->all();//名称
        $heroInfo = $ql->find('.hero-info-box .hero-info')->htmls('src')->all();//名称
        if ($heroInfo) {
            foreach ($heroInfo as $k => $val) {
                $heroInfoBox[$heroHdTitle[$k]] = QueryList::html($val)->rules(array(
                    'logo' => array('img', 'src'),
                    'link' => array('a', 'href')
                ))->range('.hero-relate-list li')->queryData();
                foreach ($heroInfoBox[$heroHdTitle[$k]] as $k2 => &$v2) {
                    $v2['link'] = 'https://pvp.qq.com/web201605/herodetail/' . $v2['link'];//链接
                    $v2['logo'] = 'http:' . $v2['logo'];//英雄图片
                    $v2['desc'] = QueryList::html($val)->find('.hero-list-desc p:eq(' . $k2 . ')')->text();
                }
            }
        }
        //出装建议
        $equipBox = [];
        $equipItemIds = $ql->find('.equip-bd .equip-list ')->attrs('data-item')->all();//关联装备id
        $equipTips = $ql->find('.equip-bd .equip-tips')->texts()->all();//描述
        if ($equipItemIds) {
            foreach ($equipItemIds as $key => $val) {
                $equipBox[$key]['equipItemIds'] = $val;//装备id
                $equipBox[$key]['equipTips'] = $equipTips[$key];
            }
        }

        $res['skinData'] = $skinData ?? [];//皮肤
        $res['scoreInfo'] = $scoreInfo ?? [];//评分
        $res['heroStory'] = $heroStory ?? '';//背景故事
        $res['history'] = $history; //英雄介绍
        $res['skillBaseInfo'] = $skillBaseInfo ?? [];//技能介绍
        $res['suggList'] = $suggList ?? [];//铭文搭配建议
        $res['addSkills'] = $addSkills ?? [];//技能加点建议
        $res['heroInfoBox'] = $heroInfoBox ?? [];//英雄关系
        $res['equipBox'] = $equipBox ?? [];//出装建议

        return $res;

        return $data;
    }
}
