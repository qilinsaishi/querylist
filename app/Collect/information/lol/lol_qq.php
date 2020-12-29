<?php

namespace App\Collect\information\lol;

class lol_qq
{
    protected $data_map =
        [
            "author_id"=>['path'=>"authorID",'default'=>''],//原站点作者ID
            "author"=>['path'=>"sAuthor",'default'=>''],//原站点作者
            "logo"=>['path'=>"avatar",'default'=>''],//logo
            "site_id"=>['path'=>"iDocID",'default'=>""],//原站点ID
            "game"=>['path'=>"",'default'=>"lol"],//对应游戏
            "source"=>['path'=>"",'default'=>"lol_qq"],//来源
            "title"=>['path'=>"sTitle",'default'=>''],//标题
            "content"=>['path'=>"sContent",'default'=>'']//内容
            ];
    public function collect($arr)
    {
        $cdata = [];
        $url = $arr['detail']['url'] ?? '';
        $res = curl_get($url);
        $res = $res['data']['result'] ?? [];
        $array=[];
        if (!empty($res)) {
            foreach ($res as $key=>$val){
                $detail_url='https://apps.game.qq.com/cmc/zmMcnContentInfo?r0=jsonp&source=web_pc&type=0&docid='.$val['iDocID'].'&_='.msectime();
                $array=curl_get($detail_url);//获取详情接口信息
                $detail_data= $array['data']['result'] ?? [];
                $target=$arr['mission_type'];//23=>'综合',24=>'公告',25=>'赛事',27=>'攻略',28=>'社区'

                $detail_data['target']= $target;
                $cdata[$key] = [
                    'mission_id' => $arr['mission_id'],//任务id
                    'content' => json_encode($detail_data),
                    'game' => $arr['game'],//游戏类型
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
    }
    public function process($arr)
    {
        $arr['content']['avatar'] = getImage($arr['content']['avatar']);
        $data = getDataFromMapping($this->data_map,$arr['content']);
        return $data;
    }
}
