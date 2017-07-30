<?php
/**
 * 编译swoole时，在configure指令中加入--enable-async-redis
 */
require_once '../Autoloader.php';
use Workerman\Worker;
use \Swoole\Redis;
$worker = new Worker('tcp://0.0.0.0:6161');
$worker->onWorkerStart = function () {
    global $client;
    $client = new Redis;
    $client->connect('127.0.0.1', 6379, function (Redis $client, $result) {
        echo "connect\n";
        var_dump($result);
        $db = 0;
        $client->select($db);
        $password = '111111';
        $client->auth($password);
    });
};
$worker->onMessage = function ($connection, $data) {
    global $client;
    $client->set('key', 'swoole', function (Redis $client, $result) {
        var_dump($result);
        $client->get('key', function (Redis $client, $result) {
            var_dump($result);
            $client->close();
        });
    });
};
Worker::runAll();