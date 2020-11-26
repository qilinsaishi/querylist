<?php

namespace App\Http\Controllers;

use App\Services\TeamCollectService;
use Illuminate\Http\Request;
use QL\QueryList;

class HomeController extends Controller
{
    public function index(){
        $ql = QueryList::get('https://baike.baidu.com/item/TS豚首电子竞技俱乐部/22867298?fr=aladdin');
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

        dd($res);



    }

}
