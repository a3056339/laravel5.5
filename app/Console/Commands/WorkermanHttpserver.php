<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Workerman\Worker;
use PHPSocketIO\SocketIO;
use Log;
use App;

class WorkermanHttpServer extends Command
{
    protected $httpserver;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'workerman:httpserver {action} {--daemonize}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'workerman httpserver';

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
     * @return mixed
     */
    public function handle()
    {
        global $argv;
        $action = $this->argument('action');
        if (!in_array($action, ['start', 'stop', 'restart', 'reload'])) {
            $this->error('Error Arguments');
            exit;
        }
        $argv[0] = 'workerman:httpserver';
        $argv[1] = $action;
        $argv[2] = $this->option('daemonize') ? '-d' : '';

        // PHPSocketIO服务
        $sender_io = new SocketIO(2120);
        // 客户端发起连接事件时，设置连接socket的各种事件回调
        $sender_io->on('connection', function ($socket) use ($sender_io) {
            // 当客户端发来登录事件时触发
            $socket->on('login', function ($uid) use ($socket,$sender_io) {
                global $uidConnectionMap;
                // 已经登录过了
                if (isset($socket->uid)) {
                    return;
                }
                // 更新对应uid的在线数据
                $uid = (string)$uid;
                if (!isset($uidConnectionMap[$uid])) {
                    $uidConnectionMap[$uid] = 0;
                }
                // 这个uid有++$uidConnectionMap[$uid]个socket连接
                ++$uidConnectionMap[$uid];
                // 将这个连接加入到uid分组，方便针对uid推送数据
                $socket->join($uid);
                $socket->uid = $uid;
                // 更新这个socket对应页面的在线数据
            });
            // 当客户端断开连接是触发（一般是关闭网页或者跳转刷新导致）
            $socket->on('disconnect', function () use ($socket) {
                if (!isset($socket->uid)) {
                    return;
                }
                global $uidConnectionMap, $sender_io;
                // 将uid的在线socket数减一
                if (--$uidConnectionMap[$socket->uid] <= 0) {
                    unset($uidConnectionMap[$socket->uid]);
                }
            });
        });

// 当$sender_io启动后监听一个http端口，通过这个端口可以给任意uid或者所有uid推送数据
        $sender_io->on('workerStart', function () use ($sender_io) {
            // 监听一个http端口
            $inner_http_worker = new Worker('http://0.0.0.0:2121');
            // 当http客户端发来数据时触发
            $inner_http_worker->onMessage = function ($http_connection, $data) use ($sender_io) {
                global $uidConnectionMap;
                $_POST = $_POST ? $_POST : $_GET;
                $info  = $_POST;

                // 推送数据的url格式 type=publish&to=uid&content=xxxx
                switch (@$info['type']) {
                    case 'publish':
                        $to = @$info['to'];
                        // 有指定uid则向uid所在socket组发送数据
                        if ($to) {
                            $sender_io->to($to)->emit('new_msg', @json_encode($info['content']));
                            // 否则向所有uid推送数据
                        } else {
                            $sender_io->emit('new_msg', @json_encode($info['content']));
                        }
                        // http接口返回，如果用户离线socket返回fail
                        if ($to && !isset($uidConnectionMap[$to])) {
                            return $http_connection->send('offline');
                        } else {
                            return $http_connection->send('ok');
                        }
                }
                return $http_connection->send('fail');
            };
            // 执行监听
            $inner_http_worker->listen();

        });

        Worker::runAll();
    }
}
