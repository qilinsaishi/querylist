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
            "title"=>['path'=>"title",'default'=>''],//标题
            "content"=>['path'=>"content",'default'=>''],//内容
            "type"=>['path'=>"type",'default'=>1],//类型
            "site_time"=>['path'=>"create_time",'default'=>""]//来源站点的时间
            ];
    protected $type = [
        1=>1,//'综合',
        2=>1,//'公告',
        3=>3,//'赛事',
        4=>2,
        5=>4,//'攻略',
    ];
    public function collect($arr)
    {
        $cdata = [];
        $url = $arr['detail']['url'] ?? '';
        $site_id = str_replace(array('https://www.dota2.com.cn/article/details/', '.html','.shtml'), '', $url);
        $site_ids = explode('/', $site_id);
        $site_id = end($site_ids);
        if($site_id==''){
            $site_id=0;
        }
        $title = $arr['title'] ?? '';
        $type = $arr['detail']['type'] ?? '';
        $qt = QueryList::get($url);
        if ($type == 'gamenews') {
            $type = 2;//官方新闻
        } elseif ($type == 'competition') {
            $type = 3;//赛事新闻
        } elseif ($type == 'news_update') {
            $type = 4;//公告
        } elseif ($type == 'raiders') {
            $type = 5;//攻略
        }else {
            $type = 1;//综合新闻
        }
        $res = $arr['detail'] ?? [];
        $res['type'] = $type;
        if($type==5){
            $content = $qt->find(".news_main .font_style")->html();
        }else{
            $content = $qt->find(".news_main .content")->html();
        }

        $res['content'] = $content;
        $res['site_id'] = $site_id;
        $res['title'] = $title ?? '';
        $create_time=$qt->find(".news_main .title h3")->text();
        $create_times=explode('【字号： 大 中 小 】',$create_time);
        $create_time=$create_times[0] ?? '';
        if($res['create_time']==''){
            $res['create_time']=$create_time;
        }

            $informationModel = new InformationModel();
            $info_count=0;
            if($site_id >0){
                $informationInfo = $informationModel->getInformationBySiteId($site_id, 'dota2', $arr['source']);
                $info_count=count($informationInfo);
            }

            if ($info_count<= 0) {
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
        return $cdata;
    }

    public function process($arr)
    {
        /**
         * $type:1=>官方新闻;3=>赛事新闻;4=>公告;
         * remark:简介
         * create_time创建时间
         * logo
         * author：作者
         * content：内容
         * site_id：文章id
         *
         */
        $arr['content']['type'] = $this->type[$arr['content']['type']];
        if(substr($arr['content']['logo'],0,1)=='/')
        {
            $arr['content']['logo'] = 'https://www.dota2.com.cn/'.$arr['content']['logo'];
        }
        $arr['content']['logo'] = getImage($arr['content']['logo']);
        $imgpreg = '/\<img.*?src\=\"([\w:\/\.]+)\"/';
        preg_match_all($imgpreg,$arr['content']['content'],$imgList);
        foreach($imgList['1']??[] as $img)
        {
            if(substr($img,0,4)!="<img")
            {
                if(substr($img,0,1)=='/')
                {
                    $img2 = 'https://www.dota2.com.cn'.$img;
                }
                else
                {
                    $img2 = $img;
                }
                $src = getImage($img2);
                $arr['content']['content'] = str_replace($img,$src,$arr['content']['content']);
            }
        }
        $data = getDataFromMapping($this->data_map,$arr['content']);
        return $data;
    }
}
