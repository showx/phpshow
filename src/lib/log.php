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
    //默认使用seaslog作为写入日志 [2 seaslog |1 file_put_contents]
    public $type = 2;
    //日志类型
    public $log_type = array("debug"=>1,"info"=>2,"notice"=>3,"error"=>4,"critical"=>5,"emergency"=>6,"alert"=>7);
    //日志起始地址
    private $pathRoot = '';
    //日志地址
    private $path = '';
    //日志目录类型
    public $default_dir_type = 'date';
    public function __construct($default_dir='' ,$type = '')
    {
        if($type == 2)
        {
            $this->type = 2;
        }else{
            if (!extension_loaded('seaslog'))
            {
                $this->type = 1;
            }else{
                $this->type = 2;
            }
        }
        //记录日志地址
        $this->setLogPath(PS_RUNTIME.'/log/');
        //二级日志地址
        if(empty($default_dir))
        {
            $this->default_dir_type = 'date';
        }else{
            $this->default_dir_type = $default_dir;
        }
    }

    /**
     * 操作的动作
     */
    public function _action($data,$type='info')
    {
        if($this->default_dir_type == 'date')
        {
            $default_dir = date("Ymd");
            $this->setLogDirName($default_dir);
        }else{
            $this->setLogDirName($this->default_dir_type);
        }
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
            file_put_contents($this->path.'/'.$date.'.log',$log,FILE_APPEND|LOCK_EX);
        }
    }

    /**
     * 记录log
     */
    public function log($data,$type='')
    {
        if($this->type==2)
        {
            \SeasLog::log($type, $data);
        }
    }

    /**
     * debug日志
     */
    public function debug($data)
    {
        $this->_action($data,__FUNCTION__);
    }

    /**
     * info日志
     */
    public function info($data)
    {
        $this->_action($data,__FUNCTION__);
    }

    /**
     * notice日志
     */
    public function notice($data)
    {
        $this->_action($data,__FUNCTION__);
    }

    /**
     * error日志
     */
    public function error($data)
    {
        $this->_action($data,__FUNCTION__);
    }

    /**
     * critical日志
     */
    public function critical($data)
    {
        $this->_action($data,__FUNCTION__);
    }

    /**
     * alert日志
     */
    public function alert($data)
    {
        $this->_action($data,__FUNCTION__);
    }

    /**
     * emergency日志
     */
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
            $this->pathRoot = $path;
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
            $this->path = $this->pathRoot."/".$name."/";
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