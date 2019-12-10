<?php

namespace App\task;

use App\client\Client;

class Task
{
    public const SERVER_PORT = 9502;
    public const TASK_PORT = 9503;

    public $server;

    public $client = null;

    public function __construct()
    {
        $this->server = new \swoole_server("127.0.0.1", self::TASK_PORT);

        $this->server->set(
            array(
                'worker_num' => 2,                //一般设置为服务器CPU数的1-4倍
                'daemonize' => 0,                 //以守护进程执行
                'max_request' => 10000,
                'dispatch_mode' => 2,
                'task_worker_num' => 4,           //task进程的数量
                "task_ipc_mode " => 3,            //使用消息队列通信，并设置为争抢模式
                "log_file" => __DIR__ . '/../logs/task.log'
            )
        );

        $this->server->on('Receive',array($this,'onReceive'));
        $this->server->on('Task',array($this,'onTask'));
        $this->server->on('Finish',array($this,'onFinish'));
        $this->server->start();
    }

    public function onReceive($server, $fd, $from_id, $data){
        $server->task($data);
    }

    public function onTask($server, $task_id, $from_id, $data) {
        if(!$this->client){
            $this->client = new Client("ws://127.0.0.1:" . self::SERVER_PORT);
        }
        $this->client->send($data);
    }

    public function onFinish($server, $task_id, $data){
        echo "Task {$task_id} finish\n";
    }
} new Task();