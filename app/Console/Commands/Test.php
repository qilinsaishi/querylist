<?php

namespace App\Console\Commands;


use App\Services\PlayerService;
use App\Services\TeamService;
use Illuminate\Console\Command;
use App\Services\Data\IntergrationService;
class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test {type} {id}';

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
        $type = ($this->argument("type")??"team_intergration");
        $id = ($this->argument("id")??"1");
        switch ($type)
        {
            case "team_intergration":
            {
                $team_ids = explode(",",$id);
                //整合两个已经整合的队伍
                //$teamInfo = (new TeamService())->merge2mergedTeam($team_ids['0'],$team_ids['1']??0);
                //整一个一个未整合的队伍到已经整合队伍中
                //$teamInfo = (new TeamService())->mergeTeam2mergedTeam($team_ids['0'],$team_ids['1']??0);
                //整合两个未整合的队伍
                //$teamInfo = (new TeamService())->merge2unmergedTeam($team_ids['0'],$team_ids['1']??0);
                $teamInfo = (new TeamService())->merge1unmergedTeam($team_ids['0']);

                print_R($teamInfo);
                die();
                //$teamInfo = (new TeamService())->disintegration($id);
                break;
            }
            case "player_intergration":
            {
                $player_ids = explode(",",$id);
                //整合两个已经整合的队伍
                //$teamInfo = (new TeamService())->merge2mergedTeam($team_ids['0'],$team_ids['1']??0);
                //整一个一个未整合的队伍到已经整合队伍中
                //$teamInfo = (new TeamService())->mergeTeam2mergedTeam($team_ids['0'],$team_ids['1']??0);
                //整合两个未整合的队伍
                //$teamInfo = (new TeamService())->merge2unmergedTeam($team_ids['0'],$team_ids['1']??0);
                $playerInfo = (new PlayerService())->merge1unmergedPlayer($player_ids['0']);

                print_R($playerInfo);
                die();
                //$teamInfo = (new TeamService())->disintegration($id);
                break;
            }
        }
    }
}
