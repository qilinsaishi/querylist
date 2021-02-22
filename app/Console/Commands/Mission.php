<?php

namespace App\Console\Commands;


use App\Services\AliyunSercies;
use App\Services\AliyunService;
use App\Services\InformationService;
use Illuminate\Console\Command;
use App\Console\Commands\Information as oInformation;
use App\Services\MissionService as oMission;
class Mission extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mission:collect {operation} {mission_type} {game} ';

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
        $operation = ($this->argument("operation")??"collect");
        $game = ($this->argument("game")??"");
        $mission_type = ($this->argument("mission_type")??"");
        switch ($operation) {
            case "collect":
                if($mission_type=='information'){
                    //资讯采集入任务表
                    (new InformationService())->insertData();
                }

                (new oMission())->collect("","",$mission_type);
                break;
            case "process":
                (new oMission())->process($game,"",$mission_type);
                break;
            case "fixImg":
                (new oMission())->fixImg();
                break;
            case "upload":
                $fileArr = ['storage/downloads/385e744509da80c73bbab5542daaab1f.jpg',
                    'storage/downloads/5f3e9aba60b9131755123e3bc4470d19.png',
                    'storage/downloads/ebddfdb2f9e8286450ecffdea5c7e4c8.jpg'];
                (new AliyunService())->upload2Oss($fileArr);
                break;
            default:

                break;
        }

    }
    public function insert(){

        return 'finish';
    }
}
