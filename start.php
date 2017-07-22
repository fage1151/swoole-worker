<?php
/**
 * run with command
 * php start.php start
 */

use \Workerman\Worker;
use \Workerman\Lib\Timer;
require_once 'vendor/autoload.php';
$worker = new Worker('http://127.0.0.1:8090');
$worker->onConnect = function($connect){
  $connect->send('sucess');
};
$worker->onMessage = function($connect,$data){
  $connect->send('123');
};

$worker->onWorkerStart = function($worker){
    Timer::add(2000,function(){
        echo 'timer'."\n";
    },[1,2,3],false);

};
$worker->count = 1;
Worker::$stdoutFile = '/tmp/oauth.log';
Worker::$logFile = __DIR__.'/workerman.log';
Worker::$pidFile = __DIR__ . "/" . str_replace('/', '_', __FILE__ ). ".pid";
// 运行所有服务
Worker::runAll();
