<?php

namespace App\Collect\information\kpl;

class pvp_qq
{
    protected $data_map =
        [
        ];

    public function collect($arr)
    {
        $cdata = [];
        $url = $arr['detail']['url'] ?? '';
        $referer_url = $arr['detail']['refeerer_detail'] ?? '';
        $res = [];
        $detail_data = curl_get($url, $referer_url);
        if ($detail_data['status'] == 0) {
            $res = $detail_data['msg'] ?? [];
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
         * linkList:相关文章
         * sAuthor：作者
         * sContent：文章内容
         * sCoverList：文章内容里面的图片
         * sCreated：创建时间
         * sTitle：标题
         * sDesc：描述
         * sIMG：缩略图片*/

        var_dump($arr);
    }
}
