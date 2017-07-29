<?php
/**
 * run with command
 * php start.php start
 */

use \Workerman\Worker;
use \Workerman\Lib\Http;

require_once '../Autoloader.php';
$worker = new Worker();

$worker->onWorkerStart = function (Worker $worker) {
    $url = 'http://www.workerman.net';
    $request_method = 'get';
    $data = ['uid'=>1];
    $http = new Http($url, $request_method,$data);
    $http->onResponse = function ($cli) {
        var_dump($cli->body);
    };
    $http->request();
};
$worker->count = 1;
Worker::$stdoutFile = '/tmp/oauth.log';
Worker::$logFile = __DIR__ . '/workerman.log';
Worker::$pidFile = __DIR__ . "/" . str_replace('/', '_', __FILE__) . ".pid";
// 运行所有服务
Worker::runAll();