<?php

namespace App\Console\Commands;


use Illuminate\Console\Command;
use App\Services\KeywordService as oKeyword;
class Rewrite extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'keyword:rewrite {game}';

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
        (new oKeyword())->rewrite($game);
    }
}
