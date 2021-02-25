<?php

namespace App\Http\Controllers;

use App\Libs\AjaxRequest;
use App\Libs\ClientServices;
use App\Models\CollectResultModel;
use App\Models\Admin\DefaultConfig;
use App\Services\Data\DataService;
use App\Services\Data\ExtraProcessService;
use App\Services\Data\RedisService;
use Illuminate\Http\Request;


use QL\QueryList;
use GuzzleHttp\Client;


class IndexController extends Controller
{

    public function index()
    {

    }

    public function get()
    {
        $data=$this->payload;
        $return = (new DataService())->getData($data);
        return $return;
    }

    public function refresh()
    {
        $redisService = new RedisService();
        $dataType = $this->request->get("dataType","defaultConfig");
        $keyName= $this->request->get("key_name","");
        $params= $this->request->get("params",'[]');

        $data = $redisService->refreshCache($dataType,json_decode($params),$keyName);
        $return=[];
        if($data) {
            $return = (new DataService())->getData($data);
        }

        return $return;
    }

    public function sitemap()
    {
        $data=$this->payload;
        $return = (new DataService())->siteMap($data);
        return $return;
    }

}
