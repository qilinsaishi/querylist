<?php

namespace App\Http\Controllers;

use App\Libs\AjaxRequest;
use App\Libs\ClientServices;
use Illuminate\Http\Request;
use QL\QueryList;
use GuzzleHttp\Client;

class HomeController extends Controller
{

    public function index()
    {
        $html=iconv('gb2312','utf-8',file_get_contents('https://pvp.qq.com/web201605/herodetail/105.shtml'));

        $ql = QueryList::html($html);
        $item_id='105';
        //皮肤
        $skinImg = $ql->find('.pic-pf-list3 ')->attr('data-imgname');
        $skiArr=explode('|',$skinImg);
        $tempSkiArr=[];
        $skinData=[];
        if($skiArr) {
           foreach ($skiArr as $key=>&$val){
               $tempSkiArr=explode('&',$val);
               $smallImg='https://game.gtimg.cn/images/yxzj/img201606/heroimg/'.$item_id.'/'.$item_id.'-smallskin-'.($key+1).'.jpg';
               $bigImg='https://game.gtimg.cn/images/yxzj/img201606/heroimg/'.$item_id.'/'.$item_id.'-bigskin-'.($key+1).'.jpg';
               $skinData[$key]['small_img']=$smallImg;//小图
               $skinData[$key]['big_img']=$bigImg;//大图
               $skinData[$key]['name']=$tempSkiArr[0] ?? '';//皮肤名称
           }
        }
        //评分能力
        $baseText= $ql->find('.cover-list-text')->texts()->all();
        $baseBars= $ql->find('.cover-list-bar .ibar')->attrs('style');
        $baseInfo=[];
        if($baseText){
            foreach ($baseText as $key=>$val){
                $baseInfo[$key]['name']=$val;
                $baseBar=str_replace(['width:','%'],'',$baseBars[$key]);
                $baseInfo[$key]['value']=$baseBar ?? 0;
            }
        }
        //背景故事
        $heroStory= $ql->find('#hero-story .pop-bd')->html();
        //英雄介绍
        $history=$ql->find('#history .pop-bd')->html();

        //技能介绍
        $baseText= $ql->find('.cover-list-text')->texts()->all();
        $skillInfo=[];
        $skillImg=$ql->find('.skill-u1 img')->attrs('src')->all();//dd($skillImg);
        //http://game.gtimg.cn/images/yxzj/img201606/heroimg/105/10500.png
        //game.gtimg.cn/images/yxzj/img201606/heroimg/105/10500.png
        $skillName=$ql->find('.skill-show .show-list .skill-name')->texts()->all();
        $skillDesc=$ql->find('.skill-show .show-list .skill-desc')->htmls()->all();
        $skillBaseInfo=[];

        if($skillImg){
            foreach ($skillImg as $key=>$val){
                if($val!='###'){
                    $skillBaseInfo[$key]['kill_img']='http:'.$val;//技能图片
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
        //铭文搭配建议
        //铭文id,这个关联必须先执行inscription 这个铭文脚本，而且必须保证ming_id 与下面的保存一致
        $sugglist = $ql->find('.sugg-u1')->attr('data-ming');
        $sugg_tips = $ql->find('.sugg-tips')->text();//铭文描述

dd($sugglist);


        return $res;
    }

    //资讯
    public function kplInfo()
    {
        $iSubType = '330';//330=>活动,329=>赛事，

        $url = 'https://apps.game.qq.com/wmp/v3.1/?p0=18&p1=searchNewsKeywordsList&page=100&pagesize=15&_=' . msectime();

        $refeerer = 'https://pvp.qq.com/web201605/searchResult.shtml';
        $data = curl_get($url, $refeerer);
        dd($data);
        $resultTotal = $data['data']['resultTotal'] ?? '';
        $resultNum = $data['data']['resultNum'] ?? '';

        //$data=curl_get('');
        $page = getLastPage($resultTotal, $resultNum);
        for ($i = 0; $i <= $page; $i++) {
            $m = $i + 1;
            $url = 'https://apps.game.qq.com/cmc/zmMcnTargetContentList?r0=jsonp&page=' . $m . '&num=16&target=24&source=web_pc&_=' . msectime();
            //echo $url.'<br/>';
            $data[$i] = $url;
        }
        dd($data);
    }






}
