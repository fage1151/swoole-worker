# swoole-worker
## What is it
此项目是workerman(v3.4.5)的swoole移植版本，移除了对pcntl,libevent,event扩展的依赖,转而使用swoole提供的swoole_process和swoole_event，定时器采用swoole的swoole_timer
## Requires
php_version >= 5.4  
A POSIX compatible operating system (Linux, OSX, BSD)  
POSIX and Swoole extensions for PHP  
swoole_version >= 1.7.14
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
### Async WebSocket Client
    <?php
    /**
     * run with command
     * php start.php start
     */
    
    use \Workerman\Worker;
    use \Workerman\Clients\Ws;
    require_once '../Autoloader.php';
    $worker = new Worker();
    
    $worker->onWorkerStart = function (Worker $worker) {
        $url = 'laychat.workerman.net:9292';
        $tcp = new Ws($url);
        $tcp->onConnect = function (\Swoole\Http\Client $client) {
            var_dump($client);
        };
        $tcp->onMessage = function (\Swoole\Http\Client $client,$data) {
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
### Async Mysql of ReactPHP
```
composer require react/mysql
```

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';
use Workerman\Worker;

$worker = new Worker('tcp://0.0.0.0:6161');
$worker->onWorkerStart = function() {
    global $mysql;
    $loop  = Worker::getEventLoop();
    $mysql = new React\MySQL\Connection($loop, array(
        'host'   => '127.0.0.1',
        'dbname' => 'dbname',
        'user'   => 'user',
        'passwd' => 'passwd',
    ));
    $mysql->on('error', function($e){
        echo $e;
    });
    $mysql->connect(function ($e) {
        if($e) {
            echo $e;
        } else {
            echo "connect success\n";
        }
    });
};
$worker->onMessage = function($connection, $data) {
    global $mysql;
    $mysql->query('show databases' /*trim($data)*/, function ($command, $mysql) use ($connection) {
        if ($command->hasError()) {
            $error = $command->getError();
        } else {
            $results = $command->resultRows;
            $fields  = $command->resultFields;
            $connection->send(json_encode($results));
        }
    });
};
Worker::runAll();
```

### Async Redis of ReactPHP
```
composer require clue/redis-react
```

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';
use Clue\React\Redis\Factory;
use Clue\React\Redis\Client;
use Workerman\Worker;

$worker = new Worker('tcp://0.0.0.0:6161');

$worker->onWorkerStart = function() {
    global $factory;
    $loop    = Worker::getEventLoop();
    $factory = new Factory($loop);
};

$worker->onMessage = function($connection, $data) {
    global $factory;
    $factory->createClient('localhost:6379')->then(function (Client $client) use ($connection) {
        $client->set('greeting', 'Hello world');
        $client->append('greeting', '!');

        $client->get('greeting')->then(function ($greeting) use ($connection){
            // Hello world!
            echo $greeting . PHP_EOL;
            $connection->send($greeting);
        });

        $client->incr('invocation')->then(function ($n) use ($connection){
            echo 'This is invocation #' . $n . PHP_EOL;
            $connection->send($n);
        });
    });
};

Worker::runAll();
```

### Aysnc dns 
    <?php
    swoole_async_dns_lookup("www.baidu.com", function($host, $ip){
        echo "{$host} : {$ip}\n";
    });


```php
require_once __DIR__ . '/vendor/autoload.php';
use Workerman\Worker;
$worker = new Worker('tcp://0.0.0.0:6161');
$worker->onWorkerStart = function() {
    global   $dns;
    // Get event-loop.
    $loop    = Worker::getEventLoop();
    $factory = new React\Dns\Resolver\Factory();
    $dns     = $factory->create('8.8.8.8', $loop);
};
$worker->onMessage = function($connection, $host) {
    global $dns;
    $host = trim($host);
    $dns->resolve($host)->then(function($ip) use($host, $connection) {
        $connection->send("$host: $ip");
    },function($e) use($host, $connection){
        $connection->send("$host: {$e->getMessage()}");
    });
};

Worker::runAll();
```

### Http client
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

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';
use Workerman\Worker;

$worker = new Worker('tcp://0.0.0.0:6161');

$worker->onWorkerStart = function() {
    global   $client;
    $loop    = Worker::getEventLoop();
    $factory = new React\Dns\Resolver\Factory();
    $dns     = $factory->createCached('8.8.8.8', $loop);
    $factory = new React\HttpClient\Factory();
    $client = $factory->create($loop, $dns);
};

$worker->onMessage = function($connection, $host) {
    global     $client;
    $request = $client->request('GET', trim($host));
    $request->on('error', function(Exception $e) use ($connection) {
        $connection->send($e);
    });
    $request->on('response', function ($response) use ($connection) {
        $response->on('data', function ($data, $response) use ($connection) {
            $connection->send($data);
        });
    });
    $request->end();
};

Worker::runAll();
```

### ZMQ of ReactPHP
```
composer require react/zmq
```

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';
use Workerman\Worker;

$worker = new Worker('text://0.0.0.0:6161');

$worker->onWorkerStart = function() {
    global   $pull;
    $loop    = Worker::getEventLoop();
    $context = new React\ZMQ\Context($loop);
    $pull    = $context->getSocket(ZMQ::SOCKET_PULL);
    $pull->bind('tcp://127.0.0.1:5555');

    $pull->on('error', function ($e) {
        var_dump($e->getMessage());
    });

    $pull->on('message', function ($msg) {
        echo "Received: $msg\n";
    });
};

Worker::runAll();
```

### STOMP of ReactPHP
```
composer require react/stomp
```

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';
use Workerman\Worker;

$worker = new Worker('text://0.0.0.0:6161');

$worker->onWorkerStart = function() {
    global   $client;
    $loop    = Worker::getEventLoop();
    $factory = new React\Stomp\Factory($loop);
    $client  = $factory->createClient(array('vhost' => '/', 'login' => 'guest', 'passcode' => 'guest'));

    $client
        ->connect()
        ->then(function ($client) use ($loop) {
            $client->subscribe('/topic/foo', function ($frame) {
                echo "Message received: {$frame->body}\n";
            });
        });
};

Worker::runAll();
```



## Available commands
```php test.php start  ```  
```php test.php start -d  ```  
![workerman start](http://www.workerman.net/img/workerman-start.png)  
```php test.php status  ```  
![workerman satus](http://www.workerman.net/img/workerman-status.png?a=123)
```php test.php stop  ```  
```php test.php restart  ```  
```php test.php reload  ```  
