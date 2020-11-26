<?php

namespace App\Collect\team\lol;

use App\Models\CollectUrlModel;
use App\Services\CollectResultService;
use App\Services\MissionService as oMission;
use QL\QueryList;

class wanplus
{
    public function collect($arr)
    {
        $resultService = new CollectResultService();

        $url = $arr['detail']['url'] ?? '';
        $res = $this->getCollectData($url);
        $cdata = [];
        if (!empty($res)) {
            $cdata = [
                'mission_id' => $arr['mission_id'],
                'content' => json_encode($res),
                'game' => $arr['game'],
                'source_link'=>$url,
                'title'=>$arr['detail']['title'] ?? '',
                'mission_type'=>$arr['mission_type'],
                'source'=>$arr['source'],
                'status' => 1,
                'update_time'=>date("Y-m-d H:i:s")

            ];
            //处理战队采集数据

            return $cdata;
        }

    }

    /**
     * @param string $url
     * @return array $res
     */
    public function getCollectData($url='')
    {
        $res=[];
        if ($url && strlen($url)>=6) {
            $ql = QueryList::get($url);
            $res['describe'] = $ql->find('.main-content  .lemma-summary')->text();//百度百科抓取 战队简介
            $res['logo'] = $ql->find('.side-content img')->src;//战队logo
            $baseInfoNames = $ql->find('.basic-info .name')->texts();//基础信息名称
            $baseInfoValues = $ql->find('.basic-info .value')->texts();//名称对应值
            $baseInfos = [];
            $tmp_arr = array();
            if ($baseInfoValues) {
                foreach ($baseInfoValues as $key => $val) {
                    $name = preg_replace("/(\s|\&nbsp\;|　|\xc2\xa0)/", " ", strip_tags($baseInfoNames[$key]));
                    $name = preg_replace('# #', '', $name);
                    if (!in_array($name, $tmp_arr)) {
                        array_push($tmp_arr, $name);
                        if (strpos($val, '主要荣誉') !== false) {
                            $arrtemp = explode('主要荣誉', $val);
                            $val = $arrtemp[1] ?? '';
                            $val = trim(trim($val, '收起'));
                        }
                        $baseInfos[$key] = [
                            'name' => $name,
                            'value' => $val
                        ];
                    }
                }
            }

            $res['base_info'] = $baseInfos;
        }
        return $res;
    }
}
