<?php

namespace App\Console\Commands;


use App\Services\AliyunSercies;
use App\Services\AliyunService;
use Illuminate\Console\Command;
use App\Services\LogService as oLog;
class Log extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mission:log {operation} {type}';

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
        $type = ($this->argument("type")??"daily");
        $operation = ($this->argument("operation")??"cutLog");
        switch ($operation) {
            case "cutLog":
                (new oLog())->cutLog($type);
                break;
            default:
                break;
        }
    }
}
