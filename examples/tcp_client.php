<?php
/**
 * run with command
 * php start.php start
 */

use \Workerman\Worker;
use \Workerman\Clients\Tcp;
require_once '../Autoloader.php';
$worker = new Worker();

$worker->onWorkerStart = function (Worker $worker) {
    $url = 'www.workerman.net:80';
    $tcp = new Tcp($url);
    $tcp->onConnect = function ($client) {
        $client->send('123');
    };
    $tcp->onReceive = function ($client,$data) {
        var_dump($data);
    };
    $tcp->connect();
};
$worker->count = 1;
Worker::$stdoutFile = '/tmp/oauth.log';
Worker::$logFile = __DIR__ . '/workerman.log';
Worker::$pidFile = __DIR__ . "/" . str_replace('/', '_', __FILE__) . ".pid";
// 运行所有服务
Worker::runAll();