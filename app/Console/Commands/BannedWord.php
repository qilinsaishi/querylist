<?php

namespace App\Console\Commands;


use App\Services\AliyunService;
use App\Services\BannedWordService;
use Illuminate\Console\Command;
class BannedWord extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'keyword:banned {type}';

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
        $operation = ($this->argument("type")??"load");
        switch ($operation) {
            case "load":
                (new BannedWordService())->LoadFromFile();
                break;
            default:

                break;
        }



    }
}
