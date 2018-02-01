<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\WorkermanHttpserver;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\CronCommand::class,
        \App\Console\Commands\OrderCommand::class,
        WorkermanHttpServer::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {

        $schedule->command('order:cron --call=close_order')->cron('* * * * *')->withoutOverlapping();//30天未付款关闭订单
        $schedule->command('order:cron --call=close_vip')->cron('* * * * *')->withoutOverlapping();//会员到期自动关闭
        $schedule->command('order:cron --call=close_major')->cron('* * * * *')->withoutOverlapping();//年度心里管家自动关闭
        $schedule->command('order:cron --call=user_amount')->cron('* * * * *')->withoutOverlapping();//7天后将老师的钱解冻开来
        $schedule->command('order:cron --call=close_word_order')->cron('* * * * *')->withoutOverlapping();//快问超过24小时未回复
        $schedule->command('order:cron --call=inform_order')->cron('* * * * *')->withoutOverlapping();//7天未投诉的不能在投诉
        $schedule->command('order:cron --call=close_phone_order')->cron('* * * * *')->withoutOverlapping();//电话订单24小时未接单退款
        $schedule->command('order:cron --call=order_notice')->cron('* * * * *')->withoutOverlapping();//7天未投诉的不能在投诉
        $schedule->command('order:cron --call=major_notice')->cron('* * * * *')->withoutOverlapping();//电话订单24小时未接单退款
        $schedule->command('order:cron --call=unbin_phone')->cron('* * * * *')->withoutOverlapping();//电话订单24小时未接单退款
    }
}
