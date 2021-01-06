<?php

namespace App\Console\Commands;


use App\Services\AliyunSercies;
use App\Services\AliyunService;
use Illuminate\Console\Command;
use App\Services\KeywordService as oKeyword;
class Keyword extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'keyword:process {type} {game}';

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
        $operation = ($this->argument("type")??"information");
        $game = ($this->argument("game")??"");
        switch ($operation) {
            case "information":
                (new oKeyword())->information($game);
                break;
            default:

                break;
        }



    }
}
