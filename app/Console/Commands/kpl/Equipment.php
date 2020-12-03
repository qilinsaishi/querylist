<?php

namespace App\Console\Commands\kpl;

use App\Services\MissionService as oMission;
use Illuminate\Console\Command;

class Equipment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kpl:equipment';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '王者荣耀装备';

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
     *type:1=>常规模式,2=>边境突围模式
     * @return int
     */
    public function handle()
    {
        /*$cList=array(
            array('url'=>'https://pvp.qq.com/web201605/js/item.json','type'=>1),
            array('url'=>'https://pvp.qq.com/zlkdatasys/data_zlk_bjtwitem.json','type'=>2)
        );
        foreach ($cList as $val){
            $data = [
                "asign_to"=>1,
                "mission_type"=>'equipment',//装备
                "mission_status"=>1,
                "game"=>'kpl',
                "source"=>'pvp_qq',//装备
                "detail"=>json_encode(
                    [
                        "url"=>$val['url'],
                        "game"=>'kpl',//王者荣耀
                        "source"=>'pvp_qq',//王者荣耀官网
                        'type'=>$val['type']
                    ]
                ),
            ];
            $insert = (new oMission())->insertMission($data);
            echo "insert:".$insert;
        }*/

        (new oMission())->collect('kpl','pvp_qq','equipment');
    }
}
