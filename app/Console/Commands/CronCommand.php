<?php

namespace App\Console\Commands;


use Illuminate\Console\Command;
use DB;
use Log;

class CronCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:cron {--call=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '定时脚本任务';


    public function __construct()
    {
        parent::__construct();
    }


}
