<?php

namespace App\Console\Commands;

use App\Services\MissionService as oMission;
use Illuminate\Console\Command;

class CpseoPlayer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:seo_player  {operation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'AZ电竞队员';

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
        // 英雄联盟队员信息
      $mission_type = 'player';
        $game = 'lol';
        $source = 'cpseo';
        //王者荣耀信息
       /*  $mission_type='player';
         $game='kpl';
         $source='cpseo';*/
        /* $data = [
              "asign_to"=>1,
              "mission_type"=>$mission_type,
              "mission_status"=>1,
              "game"=>$game,
              "source"=>$source,
              "detail"=>json_encode(
                  [
                      "url"=>'http://www.2cpseo.com/player/594',
                      "game"=>$game,//lol
                      "source"=>$source,

                  ]
              ),
          ];
          $insert = (new oMission())->insertMission($data);
          echo "insert:".$insert;*/
        (new oMission())->collect($game, $source, $mission_type);
    }
}
