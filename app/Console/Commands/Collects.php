<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class Collects extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'collects:info';

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
        $url = 'https://apps.game.qq.com/cmc/zmMcnContentInfo?r0=jsonp&source=web_pc&type=0&docid=14961237808844876072&r1=jQuery19104658286916897647_1606099368113&_=1606100893850';
// 定义采集规则
        $detailData=curl_get1($url);

        dd($detailData);
    }
}
