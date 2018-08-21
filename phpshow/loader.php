<?php
/**
 * phpshow核心加载
 * Author:show
 */
namespace phpshow;
use \helper\util as util;


//错误等级定义
error_reporting( E_ALL );
defined("PS_DEBUG") or define("PS_DEUBG","1");
defined("PS_ISAJAX") or define("PS_ISAJAX",false);
define("PS_PATH",dirname(__FILE__));
define("PS_CONFIG_PATH",PS_PATH."/config/");
define("PS_HELPER_PATH",PS_PATH."/helper/");
define("PS_RUNTIME",PS_PATH."/runtime/");
define("PS_LIB_PATH",PS_PATH."/lib/");
define("PS_CORE","1111");

//php_sapi_name()
if( PHP_SAPI == 'cli' )
{
    define('run_mode','2');
    define('lr','\n');
}else{
    define('run_mode','1');
    define('lr','<br/>');
}
require_once PS_HELPER_PATH.'/function.php';
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
    //框架配置文件
    public $config = array();

    public $ct = 'index';
    public $ac = 'index';
    public $bindings = array();
    private $loader_file = array();
    public function __construct()
    {
        $this->begin();
        spl_autoload_register(array($this, 'autoload'));
        //增加上composer加载
        require PS_PATH.'/composer/vendor/autoload.php';
        //默认必定的加载的类
        request::init();
        $this->miniroute();
        //发生异常的记录
        set_exception_handler(array('\phpshow\lib\debug','handler_debug_exception'));
        //发生错误的记录
        set_error_handler(array('\phpshow\lib\debug','handler_debug_error'), E_ALL);
        //页面结束调用
        register_shutdown_function(array($this, 'end'));
    }

    /**
     * 程序初此化
     */
    public function begin()
    {
        $this->autoloader = array(
            "ctl_" => "/control/",
            "mod_" => "/model/",
        );
        $this->starttime = microtime(true);
        $this->date_timestamp = time();
        $this->memory = memory_get_usage();
    }

    /**
     * 可以由用户自定义加载的配置
     * @param $auto_arr
     */
    public function setAuloader($auto_arr)
    {
        if($auto_arr)
        {
            $this->autolader = array_merge($this->autolader,$auto_arr);
        }
    }
    /**
     * phpshow自动加载
     * 遵守一定的规则加载
     */
    public function autoload($classname)
    {
        $classname = preg_replace("/[^0-9a-z_\/\\\\]/i", '', $classname);
        if( class_exists ( $classname ) ) {
            //已经加载过返回true
            return true;
        }
        $file = str_replace('\\', DIRECTORY_SEPARATOR, $classname).'.php';
        $file = PS_PATH.'/'.str_replace('phpshow', '', $file);
//        echo $file.lr;
        //这里加载的文件输出到debug框
        $this->loader_file[$classname] = $file;
        if (file_exists($file)) {
            require_once $file;
            return true;
        }

        $filename = basename($file);
        $filename_sub = substr($filename,0,4);
        if(array_key_exists($filename_sub,$this->autoloader))
        {
            $filepath = PS_APP_PATH.$this->autoloader[$filename_sub].$filename;
            require_once $filepath;
            return true;
        }
        return false;
    }

    /**
     * 配置文件的读取
     * 默认加载 phpshow config -> app config
     */
    public function config()
    {
        $this->config['db'] = include PS_CONFIG_PATH.'/database.php';
        $this->config['site'] = include PS_CONFIG_PATH.'/site.php';
        $this->config['route_rule'] = include PS_CONFIG_PATH.'/route_rule.php';
        //other?
    }

    /**
     * phpshow路由处理
     * 规则只有一种
     * /ct/ac
     * /module/ct/ac
     */
    public function miniroute()
    {
        $this->config();
        //也可以获取路由规则的
        //读取获取到的参数,ct,ac只能根据url来
        $url = $_SERVER['REQUEST_URI'];
        $url = parse_url($url);
        $path = $url['path'];
        $query = $url['query'] ?? '';
        $path = explode("/",$path);
        $this->ct = request::item("ct") ?? $this->ct;
        $this->ac = request::item("ac") ?? $this->ac;
        if(!empty($path['1']))
        {
            $this->ct = $path['1'];
        }
        if(!empty($path['2']))
        {
            $this->ac = $path['2'];
        }
        $this->ct = preg_replace('/([^0-9a-z_])+/is','',$this->ct);
        $this->ac = preg_replace('/([^0-9a-z_])+/is','',$this->ac);
    }

    /**
     * 输出美好的世界
     */
    public function hello()
    {
        echo 'hello world';
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
        echo lr."使用内存:".util::bunit_convert($memory - $this->memory);
        echo lr."使用时间:".sprintf('%.2f',$usetime)." sec";
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
        return call_user_func_array($this->bindings[$abstract],$parameters);
    }

    public function run()
    {

        $ctl  = 'ctl_'.$this->ct;
        if( method_exists ( $ctl, $this->ac ) === true )
        {
            $instance = new $ctl;
            $instance->{$this->ac}();
        } else {
            die('wrong place');
        }
    }

}

//App加载类
Class App{

    public static $master;
    public $result = array();
    public static function start()
    {
        self::$master = new show();
        //初始化基本集合
        self::$master->bind('db',function(){
            return new \phpshow\lib\db();
        });
        self::$master->bind('session',function(){
            return new \phpshow\lib\session();
        });
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
        return self::$master->config[$key];
    }


}
App::start();
//App::$master->hello();
