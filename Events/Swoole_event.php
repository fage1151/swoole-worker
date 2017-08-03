<?php
namespace Workerman\Events;

class Swoole_event implements EventInterface{
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
        switch ($flag) {
            default :
                $flag === self::EV_READ ? 1 : 2 ;
                if($flag == self::EV_READ){
                    if(isset($this->_allEvents[$fd_key])){
                        swoole_event_set($fd,$func);
                    }else{
                        swoole_event_add($fd,$func,null,SWOOLE_EVENT_READ);
                    }
                }else{
                    if(isset($this->_allEvents[$fd_key])){
                        swoole_event_set($fd,null,$func,SWOOLE_EVENT_WRITE);
                    }else{
                        swoole_event_add($fd,null,$func,SWOOLE_EVENT_WRITE);
                    }
                }
                $this->_allEvents[$fd_key][$flag] = $fd;
                return true;
        }

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
                if (isset($this->_allEvents[$fd_key][$flag])) {
                    swoole_event_del($this->_allEvents[$fd_key][$flag]);
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