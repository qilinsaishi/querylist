<?php

namespace App\Console\Commands;


use Illuminate\Console\Command;
use App\Services\CollectService as Collect;
use Illuminate\Support\Facades\DB;
use QL\QueryList;

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
        $url = 'https://www.dota2.com.cn/article/details/20201123/218611.html';
        $ql = QueryList::get($url);

        $content = $ql->find('.content')->html();//内容
        $content=json_encode($content);
       // $rt=(new MissionModel())->insertMission(['content'=>$content]);


        dd($content);
    }
}
