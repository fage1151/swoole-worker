<?php
/**
 * Created by PhpStorm.
 * User: heqian
 * Date: 17-7-29
 * Time: 下午3:16
 */

namespace Workerman\Lib;


class Ws
{
    public $parse_host;
    public $parse_port;
    public $client;
    public $onMessage = null;
    public $onConnect = null;
    public $onClose = null;
    public $upgrade = null;

    public function __construct($host)
    {
        $this->onMessage = function(){};
        $this->upgrade = function(){};
        $this->onClose = function(){};
        $this->onConnect = function(){};
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
        $this->client = $client = new \Swoole\Http\Client($this->parse_host, $this->parse_port);
        //设置事件回调函数
        $this->client->on("message", $this->onMessage);
        $this->client->on("connect", $this->onConnect);
        $this->client->on("close", $this->onClose);
        $this->client->upgrade('/', $this->upgrade);
    }
}