<?php

namespace App\Console\Commands\kpl;

use App\Services\MissionService as oMission;
use Illuminate\Console\Command;

class Inscription extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kpl:inscription {operation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '铭文数据';

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
            $url = 'https://pvp.qq.com/web201605/js/ming.json';
            $data = [
                "asign_to" => 1,
                "mission_type" => 'inscription',//铭文
                "mission_status" => 1,
                "game" => 'kpl',
                "source" => 'pvp_qq',//铭文
                "detail" => json_encode(
                    [
                        "url" => $url,
                        "game" => 'kpl',//王者荣耀
                        "source" => 'pvp_qq',//王者荣耀官网

                    ]
                ),
            ];
            $insert = (new oMission())->insertMission($data);
            echo "insert:" . $insert . ' lenth:' . strlen($data['detail']);
        }else{
            (new oMission())->collect('kpl','pvp_qq','inscription');
        }

    }
}
