# swoole
基于swoole的websocket推送消息框架 支持MVC框架自主推送消息

### use
```
$app_key = '766fb0be0e6d9d4f30aee774020b4d14';
$message = 'hallo';
$sign = md5($message.$app_key);

$data = ['message' => $message, 'sign' => $sign];

try {
    $client= new swoole_client(SWOOLE_SOCK_TCP);
    $client->connect('127.0.0.1', 9503, 1);

    if($client->isConnected()){
        if($client->send(json_encode($data))){
            $client->close();
            echo "ok";
        }
    }
} catch (Exception $exception){
    echo $exception->getMessage();
}
```
