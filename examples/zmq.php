<?php
/**
 * run with command
 * php start.php start
 */
/**
 * composer require swoole/zmq
 */
require_once 'vendor/autoload.php';
use Swoole\Async\ZMQ;
use Workerman\Worker;
$worker = new Worker();

$worker->onWorkerStart = function (Worker $worker) {
    //push socket:
    $zmq = new Swoole\Async\ZMQ();

    $zmq->on('Message', function ($msg)
    {
        echo "Received: $msg\n";
    });

    $zmq->bind('tcp://0.0.0.0:9530');
    //pull socket
    $zmq = new Swoole\Async\ZMQ();

    $zmq->connect('tcp://0.0.0.0:5555');

    Swoole\Timer::tick(1000, function () use ($zmq)
    {
        static $i = 0;
        $msg = "hello-" . $i++;
        echo "Sending: $msg\n";
        $zmq->send($msg);
    });
    // pubsub
    $sub = new Swoole\Async\ZMQ(ZMQ::SOCKET_SUB);
    $sub->on('Message', function ($msg)
    {
        echo "Received: $msg\n";
    });
    $sub->bind('tcp://0.0.0.0:5556');
    $sub->subscribe('foo');
    $pub = new Swoole\Async\ZMQ(ZMQ::SOCKET_PUB);
    $pub->connect('tcp://127.0.0.1:5556');
    Swoole\Timer::tick(1000, function () use ($pub)
    {
        static $i = 0;
        $msg = "foo " . $i++;
        echo "Sending: $msg\n";
        $pub->send($msg);
    });
};
$worker->count = 1;
Worker::$logFile = __DIR__ . '/workerman.log';
Worker::$pidFile = __DIR__ . "/" . str_replace('/', '_', __FILE__) . ".pid";
// 运行所有服务
Worker::runAll();