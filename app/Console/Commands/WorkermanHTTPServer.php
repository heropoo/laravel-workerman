<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Workerman\Connection\TcpConnection;
use Workerman\Worker;

class WorkermanHTTPServer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'workerman:http-server {sub_command?} {--d|daemon} {--g|gracefully}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run workerman HTTP server';

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


        Worker::$pidFile = storage_path() . '/pids/http_server.pid';
        Worker::$logFile = storage_path() . '/logs/http_server.log';
        Worker::$stdoutFile = storage_path() . '/logs/http_server-stdout.log';

        // 创建一个Worker监听2345端口，使用http协议通讯
        $http_worker = new Worker("http://0.0.0.0:2345");

        // 启动4个进程对外提供服务
        $http_worker->count = 4;

        //$http_worker->user = '_www';

        // 接收到浏览器发送的数据时回复hello world给浏览器
        $http_worker->onMessage = function(TcpConnection $connection, $data)
        {
            var_dump($data);
            // 向浏览器发送hello world
            $connection->send('hello world');
        };

        // 运行worker
        Worker::runAll();

    }
}
