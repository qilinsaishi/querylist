<?php

namespace App\Collect\information\dota2;

use App\Models\InformationModel;
use QL\QueryList;

class gamedota2
{
    protected $data_map =
        [
            "author_id"=>['path'=>"",'default'=>'0'],//原站点作者ID
            "author"=>['path'=>"author",'default'=>''],//原站点作者
            "logo"=>['path'=>"logo",'default'=>''],//logo
            "site_id"=>['path'=>"site_id",'default'=>""],//原站点ID
            "game"=>['path'=>"",'default'=>"dota2"],//对应游戏
            "source"=>['path'=>"",'default'=>"gamedota2"],//来源
            "title"=>['path'=>"remark",'default'=>''],//标题
            "content"=>['path'=>"sContent",'default'=>''],//内容
            "type"=>['path'=>"target",'default'=>1],//类型
            "site_time"=>['path'=>"create_time",'default'=>""]//来源站点的时间
            ];
    protected $type = [
        1=>1,//'综合',
        2=>2,//'公告',
        3=>3,//'赛事',
        4=>2,//'攻略',
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
        $data = getDataFromMapping($this->data_map,$arr['content']);
        print_R($data);
        die();

    }
}
