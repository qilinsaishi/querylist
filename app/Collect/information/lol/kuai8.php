<?php

namespace App\Collect\information\lol;

use App\Libs\ClientServices;
use QL\QueryList;

class kuai8
{
    //资讯攻略
    protected $data_map =
        [
            "author_id"=>['path'=>"",'default'=>0],//原站点作者ID
            "author"=>['path'=>"author",'default'=>''],//原站点作者
            "logo"=>['path'=>"img_url",'default'=>''],//logo
            "site_id"=>['path'=>"site_id",'default'=>0],//原站点ID
            "game"=>['path'=>"",'default'=>"lol"],//对应游戏
            "source"=>['path'=>"",'default'=>"kuai8"],//来源
            "title"=>['path'=>"title",'default'=>''],//标题
            "content"=>['path'=>"content",'default'=>''],//内容
            "type"=>['path'=>"",'default'=>4],//类型
            "site_time"=>['path'=>"dtime",'default'=>""]//来源站点的时间
            ];
    public function collect($arr)
    {
        $cdata = [];
        $url = $arr['detail']['url'] ?? '';
        $detail_ql=QueryList::get($url);
        $content=$detail_ql->find('.article-detail .a-detail-cont')->html();
        $title=$arr['detail']['title'] ?? '';
        $desc=$arr['detail']['desc'] ?? '';
        $img_url=$arr['detail']['img_url'] ?? '';
        $dtime=$arr['detail']['dtime'] ?? '';
        $res=[
            'title'=>$title,
            'desc'=>$desc,
            'dtime'=>$dtime,
            'source_url'=>$url,
            'img_url'=>$img_url,
            'content'=>$content ?? '',
        ];
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
        $t = explode("/",$arr['source_link']);
        $t2 = explode(".",$t[count($t)-1]);
        $arr['content']['site_id'] = $t2['0'];
        $arr['content']['logo'] = getImage($arr['content']['img_url']);
        $imgpreg = "/<img.*?src=[\"|\']?(.*?)[\"|\']?\s.*?>/i";
        preg_match($imgpreg,$arr['content']['content'],$imgList);
        foreach($imgList as $img)
        {
            if(substr($img,0,4)=="http")
            {
                $src = getImage($img);
                $arr['content']['content'] = str_replace($img,$src,$arr['content']['content']);
            }
        }
        $data = getDataFromMapping($this->data_map,$arr['content']);
        return $data;
    }
}
