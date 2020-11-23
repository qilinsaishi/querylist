<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use QL\QueryList;

class HomeController extends Controller
{
    public function index(){
       /* $url = 'https://apps.game.qq.com/cmc/zmMcnContentInfo?r0=jsonp&source=web_pc&type=0&docid=14961237808844876072&r1=jQuery19104658286916897647_1606099368113&_=1606100893850';
// 定义采集规则
        $detailData=curl_get1($url);

        dd($detailData);*/
        $command = "git checkout dev && git status  && git pull";
        (exec($command,$return));
        echo implode("\n",$return)."\n";
        unset($return);
        $command = "cd ../../../../../CommonConfig/ && cp CacheConfigDev.php CacheConfig.php";
        (exec($command,$return));
        echo implode("\n",$return)."\n";
        unset($return);
        $command = "cd ../../../../../CommonConfig/ && cp databaseConfigDev.php databaseConfig.php";
        (exec($command,$return));
        unset($return);
        $command = "cd ../../../../../CommonConfig/ && cp urlConfigDev.php urlConfig.php && ls";
        (exec($command,$return));
        echo implode("\n",$return)."\n";
        unset($return);
    }

}
