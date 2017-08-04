# swoole-worker
## What is it

[![Join the chat at https://gitter.im/swoole-worker/Lobby](https://badges.gitter.im/swoole-worker/Lobby.svg)](https://gitter.im/swoole-worker/Lobby?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)
此项目是workerman(v3.4.5)的swoole移植版本，移除了对pcntl,libevent,event扩展的依赖,转而使用swoole提供的swoole_process和swoole_event，定时器采用swoole的swoole_timer
## Requires
php_version >= 5.4  
A POSIX compatible operating system (Linux, OSX, BSD)  
POSIX and Swoole extensions for PHP  
swoole_version >= 1.7.14
## Thanks

 * [workerman](https://github.com/walkor/Workerman)  Workerman是一款纯PHP开发的开源高性能的PHP socket 服务器框架
 * [swoole](https://github.com/swoole/swoole-src)  PHP的异步、并行、高性能网络通信引擎,使用纯C语言编写

## Installation

```
composer require fage1151/swoole-worker
```

## Basic Usage
用法与workerman兼容,第三方组件可能存在不兼容的情况
### A websocket server 
```php
<?php
require_once __DIR__ . '/vendor/autoload.php';
use Workerman\Worker;

// Create a Websocket server
$ws_worker = new Worker("websocket://0.0.0.0:2346");

// 4 processes
$ws_worker->count = 4;

// Emitted when new connection come
$ws_worker->onConnect = function($connection)
{
    echo "New connection\n";
 };

// Emitted when data received
$ws_worker->onMessage = function($connection, $data)
{
    // Send hello $data
    $connection->send('hello ' . $data);
};

// Emitted when connection closed
$ws_worker->onClose = function($connection)
{
    echo "Connection closed\n";
};

// Run worker
Worker::runAll();
```

### An http server
```php
<?php
require_once __DIR__ . '/vendor/autoload.php';
use Workerman\Worker;

// #### http worker ####
$http_worker = new Worker("http://0.0.0.0:2345");

// 4 processes
$http_worker->count = 4;

// Emitted when data received
$http_worker->onMessage = function($connection, $data)
{
    // $_GET, $_POST, $_COOKIE, $_SESSION, $_SERVER, $_FILES are available
    var_dump($_GET, $_POST, $_COOKIE, $_SESSION, $_SERVER, $_FILES);
    // send data to client
    $connection->send("hello world \n");
};

// run all workers
Worker::runAll();
```

### A WebServer
```php
<?php
require_once __DIR__ . '/vendor/autoload.php';
use Workerman\WebServer;
use Workerman\Worker;

// WebServer
$web = new WebServer("http://0.0.0.0:80");

// 4 processes
$web->count = 4;

// Set the root of domains
$web->addRoot('www.your_domain.com', '/your/path/Web');
$web->addRoot('www.another_domain.com', '/another/path/Web');
// run all workers
Worker::runAll();
```

### A tcp server
```php
<?php
require_once __DIR__ . '/vendor/autoload.php';
use Workerman\Worker;

// #### create socket and listen 1234 port ####
$tcp_worker = new Worker("tcp://0.0.0.0:1234");

// 4 processes
$tcp_worker->count = 4;

// Emitted when new connection come
$tcp_worker->onConnect = function($connection)
{
    echo "New Connection\n";
};

// Emitted when data received
$tcp_worker->onMessage = function($connection, $data)
{
    // send data to client
    $connection->send("hello $data \n");
};

// Emitted when new connection come
$tcp_worker->onClose = function($connection)
{
    echo "Connection closed\n";
};

Worker::runAll();
```

### Enable SSL.
```php
<?php
require_once __DIR__ . '/vendor/autoload.php';
use Workerman\Worker;

// SSL context.
$context = array(
    'ssl' => array(
        'local_cert' => '/your/path/of/server.pem',
        'local_pk'   => '/your/path/of/server.key',
    )
);

// Create a Websocket server with ssl context.
$ws_worker = new Worker("websocket://0.0.0.0:2346", $context);

// Enable SSL. WebSocket+SSL means that Secure WebSocket (wss://). 
// The similar approaches for Https etc.
$ws_worker->transport = 'ssl';

$ws_worker->onMessage = function($connection, $data)
{
    // Send hello $data
    $connection->send('hello ' . $data);
};

Worker::runAll();
```

### Custom protocol
Protocols/MyTextProtocol.php
```php
<?php
namespace Protocols;
/**
 * User defined protocol
 * Format Text+"\n"
 */
class MyTextProtocol
{
    public static function input($recv_buffer)
    {
        // Find the position of the first occurrence of "\n"
        $pos = strpos($recv_buffer, "\n");
        // Not a complete package. Return 0 because the length of package can not be calculated
        if($pos === false)
        {
            return 0;
        }
        // Return length of the package
        return $pos+1;
    }

    public static function decode($recv_buffer)
    {
        return trim($recv_buffer);
    }

    public static function encode($data)
    {
        return $data."\n";
    }
}
```

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';
use Workerman\Worker;

// #### MyTextProtocol worker ####
$text_worker = new Worker("MyTextProtocol://0.0.0.0:5678");

$text_worker->onConnect = function($connection)
{
    echo "New connection\n";
};

$text_worker->onMessage =  function($connection, $data)
{
    // send data to client
    $connection->send("hello world \n");
};

$text_worker->onClose = function($connection)
{
    echo "Connection closed\n";
};

// run all workers
Worker::runAll();
```

### Timer
```php
<?php
require_once __DIR__ . '/vendor/autoload.php';
use Workerman\Worker;
use Workerman\Lib\Timer;

$task = new Worker();
$task->onWorkerStart = function($task)
{
    // 2.5 seconds
    $time_interval = 2.5; 
    $timer_id = Timer::add($time_interval, 
        function()
        {
            echo "Timer run\n";
        }
    );
};

// run all workers
Worker::runAll();
```

### AsyncTcpConnection (tcp/ws/text/frame etc...)
```php
<?php
require_once __DIR__ . '/vendor/autoload.php';
use Workerman\Worker;
use Workerman\Connection\AsyncTcpConnection;

$worker = new Worker();
$worker->onWorkerStart = function()
{
    // Websocket protocol for client.
    $ws_connection = new AsyncTcpConnection("ws://echo.websocket.org:80");
    $ws_connection->onConnect = function($connection){
        $connection->send('hello');
    };
    $ws_connection->onMessage = function($connection, $data){
        echo "recv: $data\n";
    };
    $ws_connection->onError = function($connection, $code, $msg){
        echo "error: $msg\n";
    };
    $ws_connection->onClose = function($connection){
        echo "connection closed\n";
    };
    $ws_connection->connect();
};
Worker::runAll();
```
### Async Tcp Client
```php
<?php
/**
 * run with command
 * php start.php start
 */

use \Workerman\Worker;
use \Workerman\Clients\Tcp;
require_once '../Autoloader.php';
$worker = new Worker();

$worker->onWorkerStart = function (Worker $worker) {
    $url = 'www.workerman.net:80';
    $tcp = new Tcp($url);
    $tcp->onConnect = function ($client) {
        $client->send('123');
    };
    $tcp->onReceive = function ($client,$data) {
        var_dump($data);
    };
    $tcp->connect();
};
$worker->count = 1;
Worker::$stdoutFile = '/tmp/oauth.log';
Worker::$logFile = __DIR__ . '/workerman.log';
Worker::$pidFile = __DIR__ . "/" . str_replace('/', '_', __FILE__) . ".pid";
// 运行所有服务
Worker::runAll();
```
### Async WebSocket Client
```php
<?php
/**
 * run with command
 * php start.php start
 */

use \Workerman\Worker;
use \Workerman\Clients\Ws;
use \Swoole\Http\Client;
require_once '../Autoloader.php';
$worker = new Worker();

$worker->onWorkerStart = function (Worker $worker) {
    $url = 'laychat.workerman.net:9292';
    $tcp = new Ws($url);
    $tcp->onConnect = function (Client $client) {
        var_dump($client);
    };
    $tcp->onMessage = function (Client $client,$data) {
        $client->push('{"type":"ping"}');
        var_dump($data);
    };
    $tcp->connect();
};
$worker->count = 1;
Worker::$stdoutFile = '/tmp/oauth.log';
Worker::$logFile = __DIR__ . '/workerman.log';
Worker::$pidFile = __DIR__ . "/" . str_replace('/', '_', __FILE__) . ".pid";
// 运行所有服务
Worker::runAll();
```
### Aysnc Dns Client
```php
<?php
require_once __DIR__ . '/vendor/autoload.php';
use Workerman\Worker;
$worker = new Worker('tcp://0.0.0.0:6161');
$worker->onWorkerStart = function() {
   swoole_async_dns_lookup("www.baidu.com", function($host, $ip){
       echo "{$host} : {$ip}\n";
   });
};
$worker->onMessage = function($connection, $host) {

};

Worker::runAll();
```

### Async Http Client
    https网站需要依赖openssl，必须在编译swoole时启用--enable-openssl
```php
<?php
/**
 * run with command
 * php start.php start
 */

use \Workerman\Worker;
use \Workerman\Clients\Http;

require_once '../Autoloader.php';
$worker = new Worker();

$worker->onWorkerStart = function (Worker $worker) {
    $url = 'http://www.workerman.net';
    $request_method = 'get';
    $data = ['uid'=>1];
    $http = new Http($url, $request_method);
    $http->onResponse = function ($cli) {
        var_dump($cli->body);
    };
    $http->request($data);
};
$worker->count = 1;
Worker::$stdoutFile = '/tmp/oauth.log';
Worker::$logFile = __DIR__ . '/workerman.log';
Worker::$pidFile = __DIR__ . "/" . str_replace('/', '_', __FILE__) . ".pid";
// 运行所有服务
Worker::runAll();
```
### Async Mysql Client
```php
<?php
require_once '../Autoloader.php';
use Workerman\Worker;
use \Swoole\Mysql;
$worker = new Worker('tcp://0.0.0.0:6161');
$worker->onWorkerStart = function () {
    global $mysql;
    $mysql = new Mysql;
    $server = array(
        'host' => '192.168.56.102',
        'port' => 3306,
        'user' => 'test',
        'password' => 'test',
        'database' => 'test',
        'charset' => 'utf8', //指定字符集
        'timeout' => 2,  // 可选：连接超时时间（非查询超时时间），默认为SW_MYSQL_CONNECT_TIMEOUT（1.0）
    );

    $mysql->connect($server, function (Mysql $db, $r) {
        if ($r === false) {
            var_dump($db->connect_errno, $db->connect_error);
            die;
        }
    });
};
$worker->onMessage = function ($connection, $data) {
    global $mysql;
    $sql = 'show tables';
    $mysql->query($sql, function (Mysql $db, $r) {
        if ($r === false) {
            var_dump($db->error, $db->errno);
        } elseif ($r === true) {
            var_dump($db->affected_rows, $db->insert_id);
        }
        var_dump($r);
    });
};
Worker::runAll();
```

### Async Redis Client
```
使用Redis客户端，需要安装hiredis库
编译swoole时，在configure指令中加入--enable-async-redis
```
Document https://wiki.swoole.com/wiki/page/p-redis.html
```php
<?php
require_once '../Autoloader.php';
use Workerman\Worker;
use \Swoole\Redis;
$worker = new Worker('tcp://0.0.0.0:6161');
$worker->onWorkerStart = function () {
    global $client;
    $client = new Redis;
    $client->connect('127.0.0.1', 6379, function (Redis $client, $result) {
        echo "connect\n";
        var_dump($result);
        $db = 0;
        $client->select($db);
        $password = '111111';
        $client->auth($password);
    });
};
$worker->onMessage = function ($connection, $data) {
    global $client;
    $client->set('key', 'swoole', function (Redis $client, $result) {
        var_dump($result);
        $client->get('key', function (Redis $client, $result) {
            var_dump($result);
            $client->close();
        });
    });
};
Worker::runAll();
```

### Document
IDE自动提示工具 swoole-ide-helper：https://github.com/eaglewu/swoole-ide-helper  
Swoole官方网站　https://wiki.swoole.com/wiki/index/prid-1  
Workerman手册　http://doc.workerman.net/

## Available commands
```php test.php start  ```  
```php test.php start -d  ```  
![workerman start](http://www.workerman.net/img/workerman-start.png)  
```php test.php status  ```  
![workerman satus](http://www.workerman.net/img/workerman-status.png?a=123)  
```php test.php stop  ```  
```php test.php restart  ```  
```php test.php reload  ```  
