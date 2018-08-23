<?php
/**
 * 日志写入类
 * Created by PhpStorm.
 * User: pengyongsheng
 * Date: 2018/8/21
 * Time: 上午10:05
 */

namespace phpshow\lib;


class log
{
    //默认使用seaslog作为写入日志
    public $type = 2;
    public $log_type = array("debug","info","NOTICE");

    public function __construct()
    {
        if (!extension_loaded('seaslog'))
        {
            $this->type = 1;
        }else{
            $this->type = 2;
        }
    }
    public function _action($data,$type='info')
    {
        if($this->type==2)
        {
            call_user_func_array("\SeasLog::{$type}",array($data));
        }
    }
    public function log($data,$type='')
    {
        if($this->type==2)
        {
            \SeasLog::log($type, $data);
        }
    }
    public function debug($data)
    {
        $this->_action($data,__FUNCTION__);
    }
    public function info($data)
    {
        $this->_action($data,__FUNCTION__);
    }
    public function notice($data)
    {
        $this->_action($data,__FUNCTION__);
    }
    public function error($data)
    {
        $this->_action($data,__FUNCTION__);
    }
    public function critical($data)
    {
        $this->_action($data,__FUNCTION__);
    }
    public function alert($data)
    {
        $this->_action($data,__FUNCTION__);
    }
    public function emergency($data)
    {
        $this->_action($data,__FUNCTION__);
    }

    /**
     * 设置日志地址
     */
    public function setLogPath()
    {

    }

}