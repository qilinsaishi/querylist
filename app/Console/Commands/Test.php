<?php

namespace App\Console\Commands;


use Illuminate\Console\Command;
use App\Services\Data\IntergrationService;
class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test {type} {id}';

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
        $type = ($this->argument("type")??"team_intergration");
        $id = ($this->argument("id")??"1");
        switch ($type)
        {
            case "team_intergration":
            {
                $teamInfo = (new IntergrationService())->getTeamInfo($id);
                print_R($teamInfo);
                break;
            }
        }
    }
}
