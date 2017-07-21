<?php
/**
 * run with command
 * php start.php start
 */

use Workerman\Worker;
require_once 'vendor/autoload.php';
$worker = new Worker('http://127.0.0.1:8090');
$worker->onMessage = function($connect,$data){
  $connect->send('123');
};
$worker->count = 1;
Worker::$stdoutFile = '/tmp/oauth.log';
Worker::$logFile = __DIR__.'/workerman.log';
Worker::$pidFile = __DIR__ . "/" . str_replace('/', '_', __FILE__ ). ".pid";
// 运行所有服务
Worker::runAll();
