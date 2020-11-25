<?php

namespace App\Http\Controllers;

use App\Services\TeamCollectService;
use Illuminate\Http\Request;
use QL\QueryList;

class HomeController extends Controller
{
    public function index(){
        $model=new TeamCollectService();
        $type=1;
        $limit=2;
        $res=$model->getCollectData($type,$limit);

        dd($res);
        $ql = QueryList::get('https://baike.baidu.com/item/DYG王者荣耀分部/24274492?fr=aladdin');

        $rt = [];
        // 采集文章标题
        $rt['title'] = $ql->find('.main-content .lemmaWgt-lemmaTitle-title>h1')->text();
        //战队描述

        $rt['description'] = $ql->find('.main-content  .lemma-summary')->html();


    }

}
