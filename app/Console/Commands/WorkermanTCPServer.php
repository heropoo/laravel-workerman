<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Workerman\Connection\TcpConnection;
use Workerman\Worker;

class WorkermanTCPServer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'workerman:tcp-server {sub_command?} {--d|daemon} {--g|gracefully}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run workerman TCP server';

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
        // 处理参数
        global $argv, $argc;
        unset($argv[0]);
        $argv = array_values($argv);
        $argc = $argc - 1;


        Worker::$pidFile = storage_path() . '/pids/tcp_server.pid';
        Worker::$logFile = storage_path() . '/logs/tcp_server.log';
        Worker::$stdoutFile = storage_path() . '/logs/tcp_server-stdout.log';

        // 创建一个Worker监听2347端口，不使用任何应用层协议
        $tcp_worker = new Worker("tcp://0.0.0.0:2347");

        // 启动4个进程对外提供服务
        $tcp_worker->count = 4;

        // 当客户端发来数据时
        $tcp_worker->onMessage = function (TcpConnection $connection, $data) {
            // 向客户端发送hello $data
            $connection->send('hello ' . $data);
        };

        // 运行worker
        Worker::runAll();

    }
}
