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
    public $log_type = array("debug"=>1,"info"=>2,"notice"=>3,"error"=>4,"critical"=>5,"emergency"=>6);
    private $path = '';
    public function __construct($default_dir='')
    {
        if (!extension_loaded('seaslog'))
        {
            $this->type = 1;
        }else{
            $this->type = 2;
        }
        $this->setLogPath(PS_RUNTIME.'/log/');
        //这里一定要有个地址
        if(empty($default_dir))
        {
            $default_dir = date("Ymd");
        }
        $this->setLogDirName($default_dir);
    }
    public function _action($data,$type='info')
    {
        if(!isset($this->log_type[strtolower($type)]))
        {
            return false;
        }
        if($this->type==2)
        {
            call_user_func_array("\SeasLog::{$type}",array($data));
        }else{
            $date = date("YmdH");
            $time = time();
            $log = "[{$type}]".$data." time:{$time}\n";
            // echo $log;exit();
            file_put_contents($this->path.'/'.$date.'.log',$log,FILE_APPEND|LOCK_EX);
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
    public function setLogPath($path = '')
    {
        if($this->type=='2')
        {
            \SeasLog::setBasePath($path);
        }else{
            $this->path = $path;
        }
    }

    /**
     * 设置日志规则
     * @param string $name
     * @return string
     */
    public function setLogDirName($name = '')
    {
        if(empty($name))
        {
            return '';
        }
        if($this->type=='2')
        {
            \SeasLog::setLogger($name);
        }else{
            $this->path = $this->path."/".$name."/";
            //要判断一下mkdir吧
            if (!is_dir($this->path)){
                $this->dir_make($this->path);
            }
        }
    }

    /**
     * 创建文件夹
     * @return bool
     */
    public function dir_make($path)
    {
        $tmp = mkdir($path,0777,true);
        return $tmp;
    }

}