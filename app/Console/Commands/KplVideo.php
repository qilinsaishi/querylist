<?php

namespace App\Console\Commands;

use App\Models\CollectResultModel;
use App\Services\MissionService as oMission;
use Illuminate\Console\Command;

class KplVideo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:kpl_video  {operation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $operation = ($this->argument("operation")??"insert");
        if($operation=='insert'){
            $collectResultModel=new CollectResultModel();
            $cdata = curl_get('https://gicp.qq.com/wmp/data/js/v3/WMP_PVP_WEBSITE_DATA_18_VIDEO_CH_V3.js');

            //$cdata=$cdata['hero'] ?? [];
            if($cdata){
                foreach ($cdata as $key=>$val){
                    if(isset($val['jData']) && $val['jData']){
                        foreach ($val['jData'] as &$v){
                            $v['url']='https://pvp.qq.com/v/detail.shtml?G_Biz=18&tid='.$v['iVideoId'];
                            if(strpos($v['sIMG'],'http') ===false){
                                $v['sIMG']='http:'.$v['sIMG'];
                            }
                            $v['tag_name']=$val['sTag'];
                            $v['game']='kpl';
                            $v['source']='pvp_qq';
                            $data = [
                                "asign_to"=>1,
                                "mission_type"=>'video',
                                "mission_status"=>1,
                                "game"=>'kpl',
                                "source"=>'pvp_qq',
                                "detail"=>json_encode($v),
                            ];
                            $insert = (new oMission())->insertMission($data);
                            echo "insert:".$insert.' lenth:'.strlen($data['detail']);

                        }
                    }
                }
            }
        }else{
            (new oMission())->collect('kpl','pvp_qq','video');
        }
    }
}
