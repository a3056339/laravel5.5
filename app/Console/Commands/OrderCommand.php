<?php

namespace App\Console\Commands;


use App\Libraries\AliyunDypls\PlsPhone;
use App\Model\AliyunPhone;
use App\Model\PhoneBin;
use App\Model\User;
use App\Model\Order;
use App\Model\UserAmount;
use App\Model\UserAmountLog;
use App\Model\UserMajor;
use App\Model\UserRefund;
use App\Model\UserTalk;
use Illuminate\Console\Command;
use App\Model\UserVipQy;
use DB;
use Log;

class OrderCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'order:cron {--call=}';

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

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $call = $this->option('call');;
        if ($call) {
            switch ($call) {
                case 'close_order':
                    $this->OrderClose();//30天未付款关闭订单
                    break;
                default:
                    # code...
                    echo 'Command Is Null';
                    break;
            }
        }
    }

    //30天未付款关闭订单
    public function OrderClose()
    {
        $time   = 30 * 3600 * 24;
        if(env('APP_ENV') == 'product'){
            $time=300;
        }
        $count  = Order::where(['status' => Order::STATUS_ZERO])->where('create_at', '<=', time() - $time)->count();
        $limit  = 500;
        $offset = 0;
        do {
            $data = Order::where(['status' => Order::STATUS_ZERO])
                ->where('create_at', '<=', time() - $time)
                ->limit($limit)
                ->offset($offset)
                ->get();
            foreach ($data as $k => $val) {
                Order::where(['order_id' => $val->order_id])->update([
                    'status'    => Order::STATUS_CLOSE,
                    'update_at' => time()
                ]);
            }
            $offset += 500;
        } while ($offset <= $count);

    }


}
/*
*/