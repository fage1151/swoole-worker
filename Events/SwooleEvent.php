<?php
namespace Workerman\Events;

class SwooleEvent implements EventInterface{
    /**
     * Event base.
     *
     * @var resource
     */
    protected $_eventBase = null;

    /**
     * All listeners for read/write event.
     *
     * @var array
     */
    protected $_allEvents = array();

    /**
     * construct
     */
    public function __construct()
    {
        $this->_eventBase = '';
    }

    /**
     * {@inheritdoc}
     */
    public function add($fd, $flag, $func, $args = array())
    {
        $fd_key    = (int)$fd;
        $flag === self::EV_READ ? 1 : 2 ;
        $f = 'swoole_event_add';
        $param = [];
        if ($flag == 1 && isset($this->_allEvents[$fd_key][2])) {
            $param = [$fd,$func,$this->_allEvents[$fd_key][2][1],SWOOLE_EVENT_READ|SWOOLE_EVENT_WRITE];
            $f = 'swoole_event_set';
        } else if ($flag == 2 && isset($this->_allEvents[$fd_key][1])) {
            $param = [$fd,$this->_allEvents[$fd_key][1][1],$func,SWOOLE_EVENT_READ|SWOOLE_EVENT_WRITE];
            $f = 'swoole_event_set';
        }else if($flag == 1){
            $param = [$fd,$func];
            $f = 'swoole_event_add';
        }else if($flag == 2){
            $param = [$fd,null,$func,SWOOLE_EVENT_WRITE];
            $f = 'swoole_event_add';
        };
        call_user_func_array($f,$param);
        $this->_allEvents[$fd_key][$flag] = [$fd,$func];
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function del($fd, $flag)
    {
        switch ($flag) {
            case self::EV_READ:
            case self::EV_WRITE:
                $fd_key = (int)$fd;
                if ($flag == 1 && isset($this->_allEvents[$fd_key][2])) {
                    $param = [$fd,null,$this->_allEvents[$fd_key][2][1],SWOOLE_EVENT_WRITE];
                    $f = 'swoole_event_set';
                } else if ($flag == 2 && isset($this->_allEvents[$fd_key][1])) {
                    $param = [$fd,$this->_allEvents[$fd_key][1][1],null,SWOOLE_EVENT_READ];
                    $f = 'swoole_event_set';
                }else {
                    $param = [$fd];
                    $f = 'swoole_event_del';
                }
                if (isset($this->_allEvents[$fd_key][$flag])) {
                    call_user_func_array($f,$param);
                    unset($this->_allEvents[$fd_key][$flag]);
                }
                if (empty($this->_allEvents[$fd_key])) {
                    unset($this->_allEvents[$fd_key]);
                }
                break;
        }
        return true;
    }
    /**
     * {@inheritdoc}
     */
    public function loop()
    {
        return true;
    }

    /**
     * Destroy loop.
     *
     * @return void
     */
    public function destroy()
    {

    }
}