<?php

namespace App\Console\Commands;


use App\Services\AliyunSercies;
use App\Services\AliyunService;
use App\Services\EquipmentService;
use App\Services\HeroService;
use App\Services\InformationService;
use App\Services\InscriptionService;
use App\Services\RunesService;
use App\Services\SummonerService;
use App\Services\TeamResultService;
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
                //资讯采集入任务表
                if($mission_type=='information'){
                    (new InformationService())->insertData();
                }
                //采集战队入库
                if($mission_type=='team'){
                    (new TeamResultService())->insertTeamData($mission_type);
                }
                //采集英雄入库
                if($mission_type=='hero'){
                    (new HeroService())->insertHeroData();
                }
                //采集装备入库
                if($mission_type=='equipment'){
                    (new EquipmentService())->insertEquipmentData();
                }
                //采集召唤师技能入库
                if($mission_type=='summoner'){
                    (new SummonerService())->insertSummonerData();
                }
                //采集lol符文入库
                if($mission_type=='runes'){
                    (new RunesService())->insertRunesData();
                }
                //采集kpl铭文入库
                if($mission_type=='inscription'){
                    (new InscriptionService())->insertInscriptionData();
                }



                (new oMission())->collect("","",$mission_type);
                break;
            case "process":
                if($game != "all")
                {
                    $gameList = [$game];
                }
                else
                {
                    $gameList = ['lol','kpl'];
                }
                foreach($gameList as $g)
                {
                    (new oMission())->process($g,"",$mission_type);
                }
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
