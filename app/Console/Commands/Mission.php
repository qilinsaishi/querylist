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
    protected $signature = 'mission:collect {operation} {mission_type} {game} {--count=} {--sleepmin=} {--sleepmax=} {--force=} {--week=0}';

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
                    (new TeamService())->insertTeamData($game,$force);
                }
                //采集队员入库
                if($mission_type=='player'){
                    (new PlayerService())->insertPlayerData($game,$force);
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
                    $week = $this->option("week")??0;
                    (new MatchService())->insertMatchData($game,$force,$week);
                }
                break;
            case "collect":
                $count = $this->option("count")??100;
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
                    if($mission_type == "information")
                    {
                        $H = date("H");
                        if(!in_array($H,[8,9,10,11,12,13,14,15,16,17,18,19,20]))
                        {
                            return;
                        }
                    }
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
                    (new RedisService())->refreshCache("informationList",['game'=>$gameList]);
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

            case "updateInformationRedirect":
                //php artisan mission:collect updateInformationRedirect  information all (修复资讯数据)
                (new InformationService())->updateInformationRedirect();//修复资讯数据
                break;
            case "updateWcaMatchListStatus":
                //当状态1时更新DOTA2 wcaMatchList数据
                //php artisan mission:collect updateWcaMatchListStatus  match dota2  (--count=50)
                $count = $this->option("count")??50;
                (new MatchService())->updateWcaMatchListStatus($game,$count);
                break;
            case "updateRecentMatch":
                //更新赛事
                $matchService=new MatchService();
                //当状态1时更新DOTA2 wcaMatchList数据
                //php artisan mission:collect updateRecentMatch  match lol  (--count=50)
                $count = $this->option("count")??50;
                if($game=='lol' || $game=='kpl'){
                    /* php artisan mission:collect updateRecentMatch  match lol  --count=50
                        (更新scoregg_match_list表里面的round_detailed=0的数据)*/
                    $matchService->updateScoreggMatchList($game,$count);

                }elseif($game=='dota2'){
                    /* php artisan mission:collect updateRecentMatch  match dota2 --count=50
                    (更新shangniu_match_list表里面的round_detailed=0的数据)*/
                    //$matchService->updateWcaMatchListStatus($game,$count);
                    $matchService->updateShangniuMatchListStatus($game, $count);

                }

                break;
            case 'doat2TournamentDisplay':
                (new MatchService())->setDota2TournamentDisplay();
                break;
            case 'autoIntergrationTeam':
                //自动整合队伍php artisan mission:collect autoIntergrationTeam  team kpl

                (new TeamService())->autoIntergrationTeam($game);
                break;

        }

    }
    public function insert(){

        return 'finish';
    }
}
