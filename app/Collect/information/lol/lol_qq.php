<?php

namespace App\Collect\information\lol;

use App\Models\InformationModel;

class lol_qq
{
    protected $data_map =
        [
            "author_id"=>['path'=>"authorID",'default'=>''],//原站点作者ID
            "author"=>['path'=>"sAuthor",'default'=>''],//原站点作者
            "logo"=>['path'=>"sIMG",'default'=>''],//logo
            "site_id"=>['path'=>"iDocID",'default'=>""],//原站点ID
            "game"=>['path'=>"",'default'=>"lol"],//对应游戏
            "source"=>['path'=>"",'default'=>"lol_qq"],//来源
            "title"=>['path'=>"sTitle",'default'=>''],//标题
            "content"=>['path'=>"sContent",'default'=>''],//内容
            "type"=>['path'=>"target",'default'=>1],//类型
            "site"=>['path'=>"",'default'=>1],//指定站点
            "site_time"=>['path'=>"sIdxTime",'default'=>""]//来源站点的时间
            ];
    protected $type = [
        23=>1,//'综合',
        24=>2,//'公告',
        25=>3,//'赛事',
        27=>4,//'攻略',
        28=>5,//'社区'
    ];

    public function collect($arr)
    {
        $cdata = [];
        $url = $arr['detail']['url'] ?? '';
        $target= $arr['detail']['target'] ?? '';
        $res=$arr['detail'] ?? [];

        if (count($res)>0) {
            $cdata = [
                'mission_id' => $arr['mission_id'],//任务id
                'content' => is_array($res) ? json_encode($res) : [],
                'game' => $arr['game'],//游戏类型
                'source_link' => $arr['source_link'] ?? $url,
                'title' => $arr['title'] ?? '',
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
        $redis = app("redis.connection");
        //target=23 ( 23=>'综合',24=>'公告',25=>'赛事',27=>'攻略',28=>'社区')
        $arr['content']['target'] = $this->type[$arr['content']['target']];
        if(substr($arr['content']['sIMG'],0,4)!="http")
        {
            $arr['content']['sIMG'] = "http:".$arr['content']['sIMG'];
        }
        $arr['content']['sIMG'] = getImage($arr['content']['sIMG'],$redis);
        $imgpreg = '/\<img.*?src\=\"([\w:\/\.]+)\"/';
        preg_match_all($imgpreg,$arr['content']['sContent'],$imgList);
        foreach($imgList['1']??[] as $img)
        {
            if(substr($img,0,4)=="http")
            {
                $src = getImage($img,$redis);
                $arr['content']['sContent'] = str_replace($img,$src,$arr['content']['sContent']);
            }
        }
        $data = getDataFromMapping($this->data_map,$arr['content']);

        return $data;
    }
}
