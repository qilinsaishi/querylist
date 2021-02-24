<?php

namespace App\Collect\information\dota2;

use App\Models\InformationModel;
use QL\QueryList;

class gamedota2
{
    protected $data_map =
        [
        ];
    public function collect($arr)
    {

        $cdata = [];
        $url = $arr['detail']['url'] ?? '';
        $site_id=str_replace(array('https://www.dota2.com.cn/article/details/','.html'),'',$url);
        $site_ids=explode('/',$site_id);
        $site_id=end($site_ids);

        $title=$arr['title'] ?? '';
        $type=$arr['detail']['type'] ?? '';
        if($type=='gamenews'){
            $type=2;//官方新闻
        }elseif($type=='competition'){
            $type=3;//赛事新闻
        }elseif($type=='news_update'){
            $type=4;//更新日志
        }else{
            $type=5;//综合新闻
        }
        $res= $arr['detail'] ?? [];
        $res['type']=$type;
        $qt=QueryList::get($url);
        $content=$qt->find(".news_main .content")->html();
        $res['content']=$content;
        $res['site_id']=$site_id;

        if($site_id>0){
            $informationModel=new InformationModel();
            $informationInfo=$informationModel->getInformationBySiteId($site_id,'dota2',$arr['source']);
            if(count($informationInfo) <=0){
                if (!empty($res)) {
                    $cdata = [
                        'mission_id' => $arr['mission_id'],
                        'content' => json_encode($res),
                        'game' => $arr['game'],
                        'source_link' => $url,
                        'title' => $title ?? '',
                        'mission_type' => $arr['mission_type'],
                        'source' => $arr['source'],
                        'status' => 1,
                        'update_time' => date("Y-m-d H:i:s")
                    ];

                }
            }
        }

        return $cdata;
    }
    public function process($arr)
    {

        /**
         * $type:1=>综合新闻;2=>官方新闻;3=>赛事新闻;4=>更新日志;
         * remark:简介
         * create_time创建时间
         * logo
         * author：作者
         * content：内容
         * site_id：文章id
         *
         */
        var_dump($arr);
    }
}
