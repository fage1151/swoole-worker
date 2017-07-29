<?php
/**
 * Created by PhpStorm.
 * User: heqian
 * Date: 17-7-29
 * Time: 下午3:16
 */

namespace Workerman\Lib;


class Tcp
{
    public $parse_host;
    public $parse_port;
    public $client;
    public $onConnecct = null;
    public $onClose = null;
    public $onReceive = null;
    public $onError = null;

    public function __construct($host)
    {
        $this->client = $client = new \Swoole\Client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_ASYNC);
        $this->onConnecct = function(){};
        $this->onError = function(){};
        $this->onReceive = function(){};
        $this->onClose = function(){};
        $this->parse_url_to_array($host);
    }

    public function parse_url_to_array($url)
    {

        $parsed_arr = parse_url($url);
        $this->parse_host = isset($parsed_arr['host']) ? $parsed_arr['host'] : '127.0.0.1';
        $this->parse_port = isset($parsed_arr['port']) ? $parsed_arr['port'] : '80';

    }

    public function connect()
    {
        //设置事件回调函数
        $this->client->on("connect", $this->onConnecct);
        $this->client->on("receive", $this->onReceive);
        $this->client->on("error", $this->onError);
        $this->client->on("close", $this->onClose);
        //发起网络连接
        $this->client->connect($this->parse_host, $this->parse_port, 0.5);
    }
}