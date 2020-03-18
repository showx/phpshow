<?php
/**
 * phpshow核心加载
 * Author:show
 */
namespace phpshow;
date_default_timezone_set('Asia/Shanghai');
//错误等级定义
error_reporting( E_ALL );
define("PS_PATH",dirname(__FILE__));
define("PS_CONFIG_PATH",PS_APP_PATH."/config/");
define("PS_RUNTIME",PS_APP_PATH."/runtime/");

//php_sapi_name()
if( PHP_SAPI == 'cli' )
{
    define('run_mode','2');
    define('lr',PHP_EOL);
}else{
    define('run_mode','1');
    define('lr','<br/>');
    $argc = '';
    $argv = [];
}
require_once PS_PATH."/helper/".'/function.php';
if ( ini_get('register_globals') )
{
    if (isset($_REQUEST['GLOBALS']) || isset($_FILES['GLOBALS'])) {
        die('fuck GLOBALS');
    }
    $noUnset= array('GLOBALS','_GET','_POST','_COOKIE','_REQUEST','_SERVER','_ENV','_FILES');
    $input=array_merge($_GET,$_POST,$_COOKIE,$_SERVER,$_ENV,$_FILES,isset($_SESSION) &&is_array($_SESSION) ?$_SESSION: array());
    foreach ($input as $k=>$v) {
        if (!in_array($k,$noUnset) && isset($GLOBALS[$k])) {
            unset($GLOBALS[$k]);
        }
    }
}
Class show{
    //框架开始时间
    private $starttime;
    private $date_timestamp;
    //框架使用内存
    public $memory = 0;
    public $ct = 'index';
    public $ac = 'index';
    public $bindings = [];
    public $args = [];
    public function __construct()
    {
        $this->begin();
        //默认必定的加载的类
        request::init();
        //发生异常的记录
        set_exception_handler(array('\phpshow\lib\debug','handler_debug_exception'));
        if(PHP_SAPI != 'cli')
        {
            //发生错误的记录
            set_error_handler(array('\phpshow\lib\debug','handler_debug_error'), E_ALL);
            //页面结束调用
            register_shutdown_function(array($this, 'end'));
        }
    }

    /**
     * 程序初始化
     */
    public function begin()
    {
        $this->starttime = microtime(true);
        $this->date_timestamp = time();
        $this->memory = memory_get_usage();
    }

    /**
     * 程序结束时的调用
     */
    public function end()
    {
        $memory = memory_get_usage();
        $endtime = microtime(true);
        $usetime = $endtime - $this->starttime;
        \phpshow\lib\debug::show_debug_error();
        $cx_string =  lr."使用内存:".\phpshow\helper\util::bunit_convert($memory - $this->memory).lr;
        $cx_string .= lr."使用时间:".sprintf('%.2f',$usetime)." sec".lr;
        if(\phpshow\lib\config::get("site")['dev2'] == 1 && PS_ISAJAX=='0')
        {
            if(run_mode=='1')
            {
                lookdata($cx_string);
            }else{
                echo $cx_string;
            }
        }
    }

    /**
     * 容器的绑定
     * @param $abstract
     * @param $concrete
     */
    public function bind($abstract,$concrete){
        $this->bindings[$abstract] = $concrete;
    }

    /**
     * 容器调用
     * @param $abstract
     * @param array $parameters
     * @return mixed
     */
    public function make($abstract,$parameters=[]){
        if(!isset($this->bindings[$abstract]))
        {
            return false;
        }
        if(empty($parameters))
        {
            return $this->bindings[$abstract];
        }
        return call_user_func_array($this->bindings[$abstract],$parameters);
    }

    /**
     * phpshow路由处理
     * 规则只有一种
     * /ct/ac
     */
    public function miniroute()
    {
        $route_rule = \phpshow\lib\config::get("route");
        //也可以获取路由规则的
        //读取获取到的参数,ct,ac只能根据url来
       if(run_mode == '1')
        {
            //QUERY_STRING 参数为s
            $path = request::item("s");
            if(empty($path) && !empty($_SERVER['PATH_INFO']))
            {
                $path = $_SERVER['PATH_INFO'];
            }
            $path = explode("/",trim($path,'/'));
            $realpath = array();
            foreach($path as $key=>$val)
            {
                $val = preg_replace("/([^\w])+/","",$val);
                if(!empty($val))
                {
                    $realpath[] = $val;
                }
            }
            $path = $realpath;
            if($path)
            {
                $this->ct = $path['0'] ?? 'index';
                $this->ac = $path['1'] ?? 'index';
            }
            array_shift($path);
            array_shift($path);
            $this->args = $path;
            //暂时不考虑使用反射获取参数
        }else{
            global $argc;
            global $argv;
            $this->ct = $argv['1'] ?? 'index';
            $this->ac = $argv['2'] ?? 'index';

            array_shift($argv);
            array_shift($argv);
            array_shift($argv);
            $this->args = $argv;

        }
        $rule_index = $this->ct."/".$this->ac;
        //路由规则的优化
        if($route_rule)
        {
            if(isset($route_rule[$rule_index]))
            {
                $route_val = $route_rule[$rule_index];
                if(strpos($route_val,"@"))
                {
                    $route_val = explode("@",$route_val);
                    $this->ct = $route_val['0'];
                    $this->ac = $route_val['1'];
                }
            }
        }
        $this->ct = preg_replace('/([^0-9a-z_])+/is','',$this->ct);
        $this->ac = preg_replace('/([^0-9a-z_])+/is','',$this->ac);
    }

    /**
     * 运行程序
     */
    public function run()
    {
        try{
            $ctl = '';
            $ctl1  = PS_APP_NAME."\\control\\".ucfirst($this->ct).'Controller';
            $ctl2  = PS_APP_NAME.'\\control\\ctl_'.$this->ct;
            if(class_exists($ctl1))
            {
                $ctl = $ctl1;
            }elseif(class_exists($ctl2))
            {
                $ctl = $ctl2;
            }
            if(empty($ctl))
            {
                throw new \Exception('control1');
            }
            //强制运行在cli下的规则
            if( method_exists ( $ctl, $this->ac ) === true )
            {
                $newctl = new $ctl;
                call_user_func_array(array($newctl, $this->ac), $this->args );
                exit();
            } else {
                throw new \Exception('fucking control..');
            }
            //todo 另外catch,这种比较难看
        }catch(\Throwable $e)
        {
            if(\phpshow\lib\config::get("site")['dev'] == '1')
            {
                // echo $ctl."|".$this->ac;
                lookdata($e);
            }
            echo $e->getMessage();exit();
        }
    }
}

//App加载类
Class loader{

    public static $master;
    public static $result = array();
    public static function start()
    {
        self::$master = new show();
        self::$master->miniroute();
        self::$master->run();
    }

    /**
     * 设置集合
     * setCollection
     */
    public static function setC($collection_name,$collection_obj)
    {
        self::$master->bind($collection_name,new $collection_obj());
    }

    /**
     * 获取指定的集合
     */
    public static function getC($collection)
    {
        return self::$master->make($collection);
    }

    /**
     * 临时读取
     * @param $key
     * @return mixed
     */
    public static function get($key)
    {
        return self::$result[$key];
    }
    /**
     * 临时存放的变量
     * @param $key
     * @param $value
     */
    public static function set($key,$value)
    {
        self::$result[$key] = $value;
    }

    /**
     * 获取本地配置
     * @param string $key
     * @return mixed
     */
    public static function getConfig($key='')
    {
        return \phpshow\lib\config::get($key);
    }

    /**
     * 获取master
     * @param $method
     * @param $arguments
     * @return mixed
     */
    public static function __callStatic($method, $arguments)
    {
        return call_user_func_array(array(self::$master,$method),$arguments);
    }
    
}