<?php

namespace App\Services;

use QL\QueryList;

class CollectResultService
{

    public function getCollectData($url='')
    {
        //
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
