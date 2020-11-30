<?php

namespace App\Http\Controllers;

use App\Services\TeamCollectService;
use Illuminate\Http\Request;
use QL\QueryList;

class HomeController extends Controller
{
    public function index(){
        $init_url='https://pvp.qq.com/ingame/all/src/data.json';
        $data=curl_get($init_url);dd($data);

        $resultTotal=$data['data']['resultTotal'] ?? '';
        $resultNum=$data['data']['resultNum'] ?? '';

        //$data=curl_get('');
        $page=getLastPage($resultTotal,$resultNum);
        for ($i=0;$i<=$page;$i++){
            $m=$i+1;
            $url='https://apps.game.qq.com/cmc/zmMcnTargetContentList?r0=jsonp&page='.$m.'&num=16&target=24&source=web_pc&_='.msectime();
            //echo $url.'<br/>';
            $data[$i]=$url;
        }
dd($data);

       /* $ql = QueryList::get('https://baike.baidu.com/item/eStar%20Gaming电子竞技俱乐部/22427996?fr=aladdin');
       // $res['describe'] = $ql->find('.main-content  .lemma-summary')->text();//百度百科抓取 战队简介
       // $ql = QueryList::get($url);
        $title=$ql->find('.main-content  .lemmaWgt-lemmaTitle-title h1')->text();
        $res['describe'] = $ql->find('.main-content  .lemma-summary')->text();//百度百科抓取 战队简介
        $res['logo'] = $ql->find('.side-content img')->src;//战队logo
        $baseInfoNames = $ql->find('.basic-info .name')->texts();//基础信息名称
        $baseInfoValues = $ql->find('.basic-info .value')->texts();//名称对应值
        $history_title= $ql->find('.main-content  .title-text:eq(0)")')->text();
        $history_title=str_replace($title,'',$history_title);
        $list = [];
        //战队历史
        $data =  $ql->find('.main-content  .title-text:eq(0)")')->parent()->next();
        $list=$this->getList($data);
        //战队成绩
        $data =  $ql->find('.main-content  .title-text:eq(1)")')->parent()->next();
        $list1=$this->getList($data);

        $performance_title= $ql->find('.main-content  .title-text:eq(1)")')->text();
        $performance_title=str_replace($title,'',$performance_title);

        $historys=[
            'title'=>$history_title,
            'content' => $list
        ];
        $team_performance = [
            'title' => $performance_title,
            'content' => $list
        ];

        $res['team_historys'] = $historys ?? [];
        $res['team_performance'] = $team_performance ?? [];


        $baseInfos = [];
        $tmp_arr = array();
        $array = [];
        if ($baseInfoValues) {
            foreach ($baseInfoValues as $key => $val) {
                $name = preg_replace("/(\s|\&nbsp\;|　|\xc2\xa0)/", " ", strip_tags($baseInfoNames[$key]));
                $name = preg_replace('# #', '', $name);
                // if (!in_array($name, $tmp_arr)) {

                if (strpos($val, '主要荣誉') !== false) {
                    $arrtemp = explode('主要荣誉', $val);
                    $val = $arrtemp[1] ?? '';
                    $val = trim(trim($val, '收起'));
                }
                $baseInfos[$key] = [
                    'name' => $name,
                    'value' => $val
                ];
                // }
            }
        }


        $res['base_info'] = $baseInfos;
dd($res);
        return $res;*/
    }


}
