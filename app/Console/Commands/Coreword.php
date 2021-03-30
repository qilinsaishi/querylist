<?php

namespace App\Console\Commands;


use Illuminate\Console\Command;
use App\Services\KeywordService as oKeyword;
class Coreword extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'keyword:coreword {game} {type}';

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
        $game = ($this->argument("game")??"");
        $type = ($this->argument("type")??"5118");
        switch($type)
        {
            case "5118":
                (new oKeyword())->coreword($game);
                break;
            case "baidu":
                (new oKeyword())->baidu_keyword($game);
                break;
        }

    }
}
