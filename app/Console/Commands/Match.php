<?php

namespace App\Console\Commands;

use App\Services\MissionService as oMission;
use Illuminate\Console\Command;

class Match extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:match {operation}';

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
        $operation = ($this->argument("operation") ?? "insert");
        if($operation=='insert'){
            $data = [
                "asign_to"=>1,
                "mission_type"=>'match',
                "mission_status"=>1,
                "game"=>'lol',
                'title'=>'',
                "source"=>'chaofan',
                "detail"=>json_encode(
                    [
                        "url"=>'https://api-pc.chaofan.com/api/v1/match/list?game_id=1',
                        "game"=>'lol',//lol
                        "source"=>'chaofan',

                    ]
                ),
            ];
            $insert = (new oMission())->insertMission($data);
            echo "insert:" . $insert . ' lenth:' . strlen($data['detail']);
        }else{
            (new oMission())->collect('lol','chaofan','match');
        }

    }
}
