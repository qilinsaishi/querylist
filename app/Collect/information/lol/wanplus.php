<?php

namespace App\Collect\information\lol;

use App\Models\InformationModel;
use App\Models\MissionModel as MissionModel;
use QL\QueryList;

class wanplus
{
    protected $data_map =
        [
            "author_id"=>['path'=>"",'default'=>'0'],//原站点作者ID
            "author"=>['path'=>"author",'default'=>''],//原站点作者
            "logo"=>['path'=>"logo",'default'=>''],//logo
            "site_id"=>['path'=>"site_id",'default'=>""],//原站点ID
            "game"=>['path'=>"",'default'=>"lol"],//对应游戏
            "source"=>['path'=>"",'default'=>"wanplus"],//来源
            "title"=>['path'=>"title",'default'=>''],//标题
            "content"=>['path'=>"content",'default'=>''],//内容
            "type"=>['path'=>"type",'default'=>1],//类型
            "site"=>['path'=>"",'default'=>1],//指定站点
            "site_time"=>['path'=>"create_time",'default'=>""]//来源站点的时间
        ];
    protected $type = [
        6=>7,//'视频'
    ];
    public function collect($arr)
    {
        $cdata = [];
        $url = $arr['detail']['url'] ?? '';
        $site_id=$arr['detail']['site_id'] ??0;
        $title = $arr['title'] ?? '';
        $type = $arr['detail']['type'] ?? '';
        $qt = QueryList::get($url);
        if ($type == 'video') {
            $type = 6;//视频
        }
        $content='';
        $res = $arr['detail'] ?? [];
        $content=$content=$qt->find('.content .ov #video-video')->html();

        if(strpos($content,'.mp4')!==false){
            $res['content'] = $content;
            $res['type'] = $type;

            $informationModel = new InformationModel();
            $info_count=0;
            if($site_id >0){
                $informationInfo = $informationModel->getInformationBySiteId($site_id, 'lol', $arr['source']);
                $info_count=count($informationInfo);
            }

            if ($info_count== 0) {
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
                return $cdata;
            }
        }else{
            $missionModel = new MissionModel();
            $rt=$missionModel->updateMission($arr['mission_id'], ['mission_status' => 3]);
            return $cdata=[];
        }

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
            if(substr($arr['content']['logo'],0,5)=='//www')
            {
                $arr['content']['logo'] = substr($arr['content']['logo'],2,strlen($arr['content']['logo'])-2);
            }
            else
            {
                $arr['content']['logo'] = 'https://www.dota2.com.cn/'.$arr['content']['logo'];
            }
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
                    // die($img);//&& substr(substr($img,0,19)=='///www.dota2.com.cn')
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
