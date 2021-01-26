<?php

namespace App\Collect\information\kpl;

class pvp_qq
{
    protected $data_map =
        [
            "author_id"=>['path'=>"authorID",'default'=>''],//原站点作者ID
            "author"=>['path'=>"sAuthor",'default'=>''],//原站点作者
            "logo"=>['path'=>"sIMG",'default'=>''],//logo
            "site_id"=>['path'=>"iNewsId",'default'=>""],//原站点ID
            "game"=>['path'=>"",'default'=>"kpl"],//对应游戏
            "source"=>['path'=>"",'default'=>"lol_qq"],//来源
            "title"=>['path'=>"sTitle",'default'=>''],//标题
            "content"=>['path'=>"sContent",'default'=>''],//内容
            "type"=>['path'=>"target",'default'=>1],//类型
            "site_time"=>['path'=>"sCreated",'default'=>""]//来源站点的时间
            ];
    protected $type = [
        1761=>1,//'综合',
        1762=>2,//'公告',
        1764=>3,//'赛事',
        1765=>4,//'攻略',
        1763=>6,//'活动'
        //1761=>新闻,1762=>公告,1763=>活动,1764=>赛事,1765=>攻略
    ];
    public function collect($arr)
    {
        $cdata = [];
        $url = $arr['detail']['url'] ?? '';
        $title=$arr['title'] ?? '';
        $type=$arr['detail']['type'] ?? '';
        $res = [];
        $detail_data = curl_get($url);
        if ($detail_data['status'] == 0) {
            $res = $detail_data['msg'] ?? [];
            if(!empty($res)){
                $res['type']=$type;
                $res['create_time']=date("Y-m-d H:i:s",time());
                unset($res['linkList']);
                //unset($res['sCoverList']);
                unset($res['sCoverMap']);
            }

        }
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
        /**
         * iNewsId:新闻id
         * sAuthor：作者
         * sContent：文章内容
         * sFaceUrl：发布者头像
         * sCoverList：文章内容里面的图片
         * sCreated：创建时间
         * sTitle：标题
         * sDesc：描述
         * type:类型;//1761=>新闻,1762=>公告,1763=>活动,1764=>赛事,1765=>攻略
         * sIMG：缩略图片*/
        //var_dump($arr);
        $arr['content']['target'] = $this->type[$arr['content']['type']];
        $arr['content']['sIMG'] = getImage($arr['content']['sIMG']);
        $imgpreg = "/<img.*?src=[\"|\']?(.*?)[\"|\']?\s.*?>/i";
        preg_match($imgpreg,$arr['content']['sContent'],$imgList);
        foreach($imgList as $img)
        {
            if(substr($img,0,4)=="http")
            {
                $src = getImage($img);
                $arr['content']['sContent'] = str_replace($img,$src,$arr['content']['sContent']);
            }
        }
        $data = getDataFromMapping($this->data_map,$arr['content']);
        return $data;
    }
}
