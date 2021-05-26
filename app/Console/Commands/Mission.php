<?php

namespace App\Console\Commands;


use App\Services\AliyunSercies;
use App\Services\AliyunService;
use App\Services\Data\RedisService;
use App\Services\EquipmentService;
use App\Services\HeroService;
use App\Services\InformationService;
use App\Services\KeywordService as oKeyword;
use App\Services\InscriptionService;
use App\Services\MatchService;
use App\Services\PlayerService;
use App\Services\RunesService;
use App\Services\ScheduleService;
use App\Services\SummonerService;
use App\Services\TeamService;
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
    protected $signature = 'mission:collect {operation} {mission_type} {game} {--count=} {--sleepmin=} {--sleepmax=} {--force=}';

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
        $force = $this->option("force")??0;
        switch ($operation) {
            case "insert_mission":
                //资讯采集入任务表
                if($mission_type=='information'){
                    (new InformationService())->insertData($game,$force);
                }

                //采集战队入库
                if($mission_type=='team'){
                    (new TeamService())->insertTeamData($mission_type,$game,$force);
                }
                //采集队员入库
                if($mission_type=='player'){
                    (new PlayerService())->insertPlayerData($mission_type,$game);
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
                //采集赛事入库
                if($mission_type=='schedule'){
                    (new ScheduleService())->insertScheduleData($game,$force);
                }
                //采集赛事详情入库
                if($mission_type=='match'){
                    (new MatchService())->insertMatchData($game,$force);
                }
                break;
            case "collect":
                $count = $this->option("count")??1000;
                (new oMission())->collect($game,"",$mission_type,$count);
                break;
            case "process":
                if($game != "all")
                {
                    $gameList = [$game];
                }
                else
                {
                    $gameList = ['lol','kpl','dota2','csgo'];
                }
                foreach($gameList as $g)
                {
                    $count = $this->option("count")??100;
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
                        $oKeyword->rewrite($g);
                        $oKeyword->coreword($g);
                        $oKeyword->baidu_keyword($g);
                        $oKeyword->tfIdf($g);
                    }
                }
                break;
            case "unpublished":
                //php artisan mission:collect unpublished  information all (手动发布脚本命令)
                (new InformationService())->unPublishedList();//更新预发布脚本
                break;
            case "views":
                //php artisan mission:collect views  update all (保存缓存中的浏览数据)
                (new RedisService())->saveViews();
                break;
            case "updateScoreggMatchList":
                //php artisan mission:collect updateScoreggMatchList  match lol  (--count=50)(更新scoregg_match_list表里面的result_list数据)
                $count = $this->option("count")??50;
                (new MatchService())->updateScoreggMatchList($game,$count);
                break;
            case "updateScoreggMatchListStatus":
                //当状态不等于未结束时（status!=2）,必须要重新生成任务爬取数据
                //php artisan mission:collect updateScoreggMatchListStatus  match lol  (--count=50)
                $count = $this->option("count")??50;
                (new MatchService())->updateScoreggMatchListStatus($game,$count);
                break;

            default:

                break;
        }

    }
    public function insert(){

        return 'finish';
    }
}
