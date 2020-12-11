<?php

namespace App\Console\Commands;

use App\Services\MissionService as oMission;
use Illuminate\Console\Command;
use QL\QueryList;

class CpseoTeam extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:seo_team';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

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
        $mission_type='team';
        $game='lol';
        $source='cpseo';
       /*$count=3;
        for ($i=0;$i<=$count;$i++){
            $m=$i+1;
            $url='http://www.2cpseo.com/teams/lol/p-'.$m;
            $ql = QueryList::get($url);
            $links=$ql->find('.hot-teams-container a')->attrs('href')->all();
            if($links){
                foreach ($links as $v){
                    $data = [
                        "asign_to"=>1,
                        "mission_type"=>$mission_type,//赛事
                        "mission_status"=>1,
                        "game"=>$game,
                        "source"=>$source,//
                        "detail"=>json_encode(
                            [
                                "url"=>$v,
                                "game"=>$game,
                                "source"=>$source,
                            ]
                        ),
                    ];
                    $insert = (new oMission())->insertMission($data);
                    echo "insert:".$insert;
                }
            }

        }*/
        (new oMission())->collect($game,$source,$mission_type);
    }
}
