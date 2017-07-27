<?php
/**
 * run with command
 * php start.php start
 */

use \Workerman\Worker;
use \Workerman\Lib\Timer;
require_once '../Autoloader.php';
$worker = new Worker('tcp://127.0.0.1:8091');
$worker->onConnect = function (\Workerman\Connection\ConnectionInterface $connect) {
    $connect->send('connect success');
};
$worker->onMessage = function (\Workerman\Connection\ConnectionInterface $connect, $data) {
    $connect->send($data);
};

$worker->onWorkerStart = function (Worker $worker) {
    $timerid = Timer::add(2000,function()use(&$timerid){
        echo $timerid."\n";
    },[1,2,3],true);

};
$worker->reusePort = true;
$worker->count = 1;
Worker::$stdoutFile = '/tmp/oauth.log';
Worker::$logFile = __DIR__ . '/workerman.log';
Worker::$pidFile = __DIR__ . "/" . str_replace('/', '_', __FILE__) . ".pid";
// 运行所有服务
Worker::runAll();