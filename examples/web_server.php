<?php
/**
 * run with command
 * php start.php start
 */

use \Workerman\Worker;

require_once '../workerman/Autoloader.php';
$worker->count = 1;
$worker = new \Workerman\WebServer('http://0.0.0.0:8093');
$worker->onWorkerStart = function (Worker $worker) {


};
$worker->addRoot('127.0.0.1',__DIR__.'/web/');
$worker->reusePort = true;
$worker->count = 1;
Worker::$stdoutFile = '/tmp/oauth.log';
Worker::$logFile = __DIR__ . '/workerman.log';
Worker::$pidFile = __DIR__ . "/" . str_replace('/', '_', __FILE__) . ".pid";
// 运行所有服务
Worker::runAll();