<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use QL\QueryList;

class HomeController extends Controller
{
    public function index(){

        $ql = QueryList::get('https://baike.baidu.com/item/DYG王者荣耀分部/24274492?fr=aladdin');

        $rt = [];
// 采集文章标题
        $rt['title'] = $ql->find('.main-content .lemmaWgt-lemmaTitle-title>h1')->text();
// 采集文章作者

        $rt['description'] = $ql->find('.main-content  .lemma-summary')->html();

        dd($rt);
    }

}
