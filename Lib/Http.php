<?php
/**
 * Created by PhpStorm.
 * User: heqian
 * Date: 17-7-28
 * Time: 下午8:26
 */

namespace Workerman\Lib;


class Http
{
    public $parse_scheme;
    public $parse_host;
    public $parse_port;
    public $parse_path;
    public $parse_query;
    public $real_ip;
    public $client;
    public $request_headers = [];
    public $request_cookies = [];
    public $request_data = [];
    public $request_method = '';
    public $onResponse = null;
    public $onError = null;
    public function __construct($url,$request_method='get',$data=[],$headers=[],$cookies=[])
    {
        $this->parse_url_to_array($url);
        $available_methods = ['post','get'];
        if(!in_array($request_method,$available_methods)){
            throw new \Exception('request method is inavailable');
        }
        $this->request_headers = $headers;
        $this->request_data = $data;
        $this->request_cookies = $cookies;
        $this->request_method = $request_method;

    }
    public function parse_url_to_array($url)
    {

        $parsed_arr = parse_url($url);
        $this->parse_scheme = isset($parsed_arr['scheme']) ? $parsed_arr['scheme'] : 'http';
        $this->parse_host = isset($parsed_arr['host']) ? $parsed_arr['host'] : '127.0.0.1';
        $this->parse_port = isset($parsed_arr['port']) ? $parsed_arr['port'] : $this->parse_scheme == 'https'?'443':'80';
        $this->parse_path = isset($parsed_arr['path']) ? $parsed_arr['path'] : '/';
        $this->parse_query = isset($parsed_arr['query']) ? $parsed_arr['query'] : '';

    }

    public function request()
    {
        swoole_async_dns_lookup($this->parse_host, function($host, $ip){
            if($ip == ''){
                call_user_func_array($this->onError,[$host]);
                return;
            }
            $this->real_ip = $ip;
            if($this->parse_scheme === 'https'){
                $this->ssl = true;
            };
            $this->client = new \Swoole\Http\Client($this->real_ip, $this->parse_port,false);
            $this->client->setHeaders($this->request_headers);
            $this->client->setCookies($this->request_cookies);
            $request_method = $this->request_method;
            if($request_method == 'post'){
                $this->client->post($this->parse_path.'?'.$this->parse_query,$this->request_data,$this->onResponse);
            }else{
                $this->client->get($this->parse_path.'?'.$this->parse_query,$this->onResponse);
            }
        });
    }
}