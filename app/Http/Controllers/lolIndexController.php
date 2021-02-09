<?php

namespace App\Http\Controllers;

use App\Libs\AjaxRequest;
use App\Libs\ClientServices;
use App\Models\CollectResultModel;
use App\Models\Admin\DefaultConfig;
use App\Services\Data\DataService;
use App\Services\Data\ExtraProcessService;
use Illuminate\Http\Request;


use QL\QueryList;
use GuzzleHttp\Client;


class lolIndexController extends Controller
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
        $params = json_decode($this->request->get("params",""),true);
        $dataType = $params['dataType'] ?? 'defaultConfig';
        $keyName= $params['key_name'] ?? '';
        $redisService->refreshCache($dataType,[],$keyName);
    }

}
