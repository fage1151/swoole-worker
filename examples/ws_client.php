<?php
/**
 * run with command
 * php start.php start
 */

use \Workerman\Worker;
use \Workerman\Lib\Ws;
require_once '../Autoloader.php';
$worker = new Worker();

$worker->onWorkerStart = function (Worker $worker) {
    $url = 'laychat.workerman.net:9292';
    $tcp = new Ws($url);
    $tcp->onConnect = function (\Swoole\Http\Client $client) {
        var_dump($client);
    };
    $tcp->onMessage = function (\Swoole\Http\Client $client,$data) {
        $client->push('{"type":"ping"}');
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