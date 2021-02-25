<?php

namespace App\Console\Commands;


use App\Services\AliyunSercies;
use App\Services\AliyunService;
use App\Services\EquipmentService;
use App\Services\HeroService;
use App\Services\InformationService;
use App\Services\KeywordService as oKeyword;
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
    protected $signature = 'mission:collect {operation} {mission_type} {game} {--count=} {--sleepmin=} {--sleepmax=}';

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
               // exit;
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
                    $count = $this->option("count")??2;
                    $sleepmin = $this->option("sleepmin")??1;
                    $sleepmax = $this->option("sleepmax")??2;
                    (new oMission())->process($g,"",$mission_type,$count,$sleepmin,$sleepmax);
                }
                if($mission_type == "information")
                {
                    $oKeyword = new oKeyword();
                    foreach($gameList as $g)
                    {
                        $oKeyword->information($g);
                        $oKeyword->tfIdf($g);
                    }
                }
                break;
            default:

                break;
        }

    }
    public function insert(){

        return 'finish';
    }
}
