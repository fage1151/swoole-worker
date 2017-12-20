<?php
Swoole\Coroutine::create(function() {
    $redis = new Swoole\Coroutine\Redis();
    $res = $redis->connect('127.0.0.1', 6379);
    $ret = $redis->incr('coroutine');
    $redis->close();
    Swoole\Coroutine::create(function() {
        $redis = new Swoole\Coroutine\Redis();
        $res = $redis->connect('127.0.0.1', 6379);
        $ret = $redis->set('coroutine_i', 50);
        $redis->close();
    });
});