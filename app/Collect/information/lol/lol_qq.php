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
        $res = curl_get($url);
        $res = $res['data']['result'] ?? [];
        $array=[];
        if (!empty($res)) {
            foreach ($res as $key=>$val){
                $detail_url='https://apps.game.qq.com/cmc/zmMcnContentInfo?r0=jsonp&source=web_pc&type=0&docid='.$val['iDocID'];
                $array=curl_get($detail_url);//获取详情接口信息
                $detail_data= $array['data']['result'] ?? [];
                //23=>'综合',24=>'公告',25=>'赛事',27=>'攻略',28=>'社区'
                //判断资讯是否存在
                $site_id=$detail_data[$this->data_map['site_id']['path']] ?? 0;
                if($site_id>0){
                    $informationModel=new InformationModel();
                    $informationInfo=$informationModel->getInformationBySiteId($site_id,'lol',$arr['source']);
                    if(count($informationInfo) <=0){
                        if(($detail_data && strlen($detail_data['sContent']) >150) ){
                            $detail_data['sCreated']=date("Y-m-d H:i:s");
                            $detail_data['target']= $target;
                            $cdata[$key] = [
                                'mission_id' => $arr['mission_id'],//任务id
                                'content' => json_encode($detail_data),
                                'game' => $arr['game'],//游戏类型
                                'source_link' => $detail_url,
                                'title' => $arr['detail']['title'] ?? '',
                                'mission_type' => $arr['mission_type'],
                                'source' => $arr['source'],
                                'status' => 1,
                                'update_time' => date("Y-m-d H:i:s")

                            ];
                        }
                    }else{
                        continue;
                    }
                }

            }

            return $cdata;
        }
    }
    public function process($arr)
    {
        //target=23 ( 23=>'综合',24=>'公告',25=>'赛事',27=>'攻略',28=>'社区')
        $arr['content']['target'] = $this->type[$arr['content']['target']];
        if(substr($arr['content']['sIMG'],0,4)!="http")
        {
            $arr['content']['sIMG'] = "http:".$arr['content']['sIMG'];
        }
        $arr['content']['sIMG'] = getImage($arr['content']['sIMG']);
        $imgpreg = '/\<img.*?src\=\"([\w:\/\.]+)\"/';
        preg_match_all($imgpreg,$arr['content']['sContent'],$imgList);
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
