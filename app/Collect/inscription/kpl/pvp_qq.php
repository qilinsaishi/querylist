<?php

namespace App\Collect\inscription\kpl;

use App\Libs\ClientServices;

class pvp_qq
{
    protected $data_map =
        [
            "inscription_name" => ['path' => "ming_name", 'default' => ''],
            "description" => ['path' => "ming_des", 'default' => '暂无'],
            "cn_name" => ['path' => "ming_name", 'default' => ''],//中文名
            "en_name" => ['path' => "", 'default' => ''],//英文名
            "type" => ['path' => "ming_type", 'default' => ""],
            "logo" => ['path' => "logo", 'default' => ""],
            "aka" => ['path' => "", 'default' => ""],//别名
            "id" => ['path' => "ming_id", 'default' => 0],//对应站点ID
            "grade" => ['path' => "ming_grade", 'default' => 0],
        ];

    public function collect($arr)
    {
        $url = $arr['detail']['url'] ?? '';
        $client = new ClientServices();
        $res = $client->curlGet($url);//curl获取json数据
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

        /**
         * 铭文数据
         * "ming_id" => "1504"
         * "ming_type" => "red"
         * "ming_grade" => "5"
         * "ming_name" => "异变"
         * "ming_des" => "<p>物理攻击力+2</p><p>物理穿透+3.6</p>"
         * 'ming_img'=>'https://game.gtimg.cn/images/yxzj/img201606/mingwen/1504.png'
         */
        $data = [];
        foreach($arr['content'] as $key => $value)
        {
            $value['ming_des'] = preg_replace("/<([a-zA-Z]+)[^>]*>/", "",$value['ming_des']);
            $value['ming_des'] = preg_replace("{</([a-zA-Z]+)[^>]*>}", "",$value['ming_des']);
            $value['logo'] = "https://game.gtimg.cn/images/yxzj/img201606/mingwen/".$value['ming_id'].".png";
            $data[$key] = getDataFromMapping($this->data_map, $value);
        }
        return $data;
    }
}
