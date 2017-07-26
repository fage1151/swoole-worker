<?php
/**
 * run with command
 * php start.php start
 */

use \Workerman\Worker;

require_once '../workerman/Autoloader.php';
$worker = new Worker('http://127.0.0.1:8093');
$worker->onConnect = function (\Workerman\Connection\ConnectionInterface $connect) {
    $connect->send('connect success');
};
$worker->onMessage = function (\Workerman\Connection\ConnectionInterface $connect, $data) {
    $connect->send($data);
};

$worker->onWorkerStart = function (Worker $worker) {


};
$worker->reusePort = true;
$worker->count = 1;
Worker::$stdoutFile = '/tmp/oauth.log';
Worker::$logFile = __DIR__ . '/workerman.log';
Worker::$pidFile = __DIR__ . "/" . str_replace('/', '_', __FILE__) . ".pid";
// 运行所有服务
Worker::runAll();