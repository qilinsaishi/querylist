<?php

namespace App\Http\Controllers;

use App\Libs\AjaxRequest;
use App\Libs\ClientServices;
use App\Models\CollectResultModel;
use App\Models\Admin\DefaultConfig;
use Illuminate\Http\Request;


use QL\QueryList;
use GuzzleHttp\Client;

use App\Services\Data\PrivilegeService;

class lolIndexController extends Controller
{

    public function index()
    {

    }
    //matchList 比赛 page/page_size
    //teamList 战队 page/page_size/game
    //tournament 赛事 page/page_size
    //player 选手 page/page_size/game

    public function get()
    {
        $model=new DefaultConfig();
        try{
            $a=$model->getDefaultById(3);dd($a);
        }catch (\Exception $e){
            dd($e->getMessage());
        }


        $data = ["defaultConfig"=>["contact_us","phone_num","address"],"matchList" => ["page" => 1, "page_size" => 10],
           "tournament"=>["page" => 1, "page_size" => 10], "teamList" => ["game" => 'lol', "page" => 1, "page_size" => 10]];

        $privilegeService = new PrivilegeService();
        $data=$this->payload;print_r($data);exit;
        $return = [];
        $functionList = $privilegeService->getFunction($data);
        foreach ($functionList as $dataType => $functionInfo)
        {
            $class = $functionInfo['class'];
            $function = $functionInfo['function'];
            $params = $data[$dataType];
            $d = $class->$function($params);
            $functionCount = $functionInfo['functionCount'];
            $functionProcess = $functionInfo['functionProcess']??"";

            if(!$functionCount || $functionCount=="")
            {
                $count = 0;
            }
            else
            {
                $count = $class->$functionCount($params);
            }
            if($functionProcess!="")
            {
                $d = $privilegeService->$functionProcess($d,$functionList);
            }
            $return[$dataType] = ['data'=>$d,'count'=>$count];
        }
        return $return;
        //$name = (new Request())->post("data","here");
        //v//ar_dump($name);

        //$name2 = Input::all();
        //($name2);
        //print_R($result_list);
    }

}
