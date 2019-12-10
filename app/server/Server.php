<?php

namespace App\server;

class Server {

    public const APP_KEY = '766fb0be0e6d9d4f30aee774020b4d14';
    public const SERVER_PORT = 9502;
    public $server;

    public function __construct()
    {
        $this->server = new \Swoole\WebSocket\Server("0.0.0.0", self::SERVER_PORT);
        $this->server->on('open', array($this, 'onOpen'));
        $this->server->on('message', array($this, 'onMessage'));
        $this->server->on('close', array($this, 'onClose'));
        $this->server->start();
    }

    public function onOpen($server, $req)
    {
        foreach ($server->connections as $fd) {
            if ($server->isEstablished($fd) && $fd != $req->fd) {
                $server->push($fd, $this->message_format('in', $fd, $req->fd . '入群'));
            }
        }
        $server->push($req->fd, $this->message_format('in', $req->fd, '加群成功'));
    }

    public function onMessage($server, $frame)
    {
        $data = json_decode($frame->data, true);
        if(!isset($data['message']) || !isset($data['sign'])){
            return;
        }
        if(!$this->verifySign($data)){
            return;
        }
        foreach ($server->connections as $fd) {
            if ($server->isEstablished($fd) && $fd != $frame->fd) {
                $server->push($fd, $this->message_format('text', $frame->fd, $data['message']));
            }
        }
    }

    public function onClose($server, $fd)
    {
        foreach ($server->connections as $item) {
            if ($server->isEstablished($item) && $fd != $item) {
                $server->push($item, $this->message_format('out', $fd, $fd . 'out'));
            }
        }
    }

    public function message_format($type, $client, $data)
    {
        return json_encode([
            'type' => $type,
            'client' => $client,
            'data' => $data
        ]);
    }

    public function verifySign(array $content):bool
    {
        $message = $content['message'];
        $_sign = md5($message.self::APP_KEY);
        if($_sign != $content['sign']){
            return false;
        }
        return true;
    }
} new Server();




