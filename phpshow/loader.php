<?php
namespace phpshow;
define("PS_PATH",dirname(__FILE__));
define("PS_CONFIG_PATH",dirname(__FILE__)."/config/");
define("PS_HELPER_PATH",dirname(__FILE__)."/helper/");
define("PS_LIB_PATH",dirname(__FILE__)."/lib/");
define("PS_CORE","1111");

/**
 * phpshow核心加载类
 * Author:show
 */

// 严格开发模式
error_reporting( E_ALL );
//开启register_globals会有诸多不安全可能性，因此强制要求关闭register_globals
if ( ini_get('register_globals') )
{
    exit('php.ini register_globals must is Off! ');
}
/**
 * Class show
 * 框架的核心加载
 */
Class show{
    //框架开始时间
    private $starttime;
    private $endtime;
    private $date_timestamp;
    //框架使用内存
    public $memory = 0;
    //框架错误代码
    public $errno = 0;
    //框架配置文件
    public $config = array();
    public $result = array();
    public $ct = 'index';
    public $ac = 'index';
    public function __construct()
    {
        $this->starttime = microtime();
        $this->date_timestamp = time();
        $this->memory = memory_get_usage();
        if( PHP_SAPI == 'cli' )
        {
            define('run_mode','2');
        }else{
            define('run_mode','1');
        }
        spl_autoload_register(array($this, 'autoload'));
        //增加上composer加载
        require PS_PATH.'/composer/vendor/autoload.php';
        //默认必定的加载的类
        include_once PS_PATH.'/request.php';
        include_once PS_PATH.'/response.php';
        include_once PS_PATH.'/debug.php';
        request::init();
        $this->route();
        //发生异常的记录
        set_exception_handler(array('\phpshow\debug','handler_debug_exception'));
        //发生错误的记录
        set_error_handler(array('\phpshow\debug','handler_debug_error'), E_ALL);
        //页面结束调用
        register_shutdown_function(array($this, 'page_close'));
    }
    public function convert($size)
    {
        $unit=array('b','kb','mb','gb','tb','pb');
        return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
    }
    /**
     * phpshow自动加载
     * 遵守一定的规则加载
     */
    public function autoload($classname)
    {
        $classname = preg_replace("/[^0-9a-z_]/i", '', $classname);
        if( class_exists ( $classname ) ) {
            //已经加载过返回true
            return true;
        }
        $classfile = $classname.'.php';
        $file = str_replace('\\', DIRECTORY_SEPARATOR, $classname).'.php';
        if (file_exists($file)) {
            require $file;
            return true;
        }
        return false;
    }

    /**
     * 配置文件的读取
     */
    public function config()
    {
        //默认加载 phpshow config -> app config;
        $this->config['db'] = include PS_CONFIG_PATH.'/database.php';
        $this->config['site'] = include PS_CONFIG_PATH.'/site.php';
//        $this->config['route_rule'] = include PS_CONFIG_PATH.'/route_rule.php';
    }
    /**
     * 临时读取
     * @param $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->result[$key];
    }

    /**
     * 临时存放的变量
     * @param $key
     * @param $value
     */
    public function set($key,$value)
    {
        $this->result[$key] = $value;
    }
    /**
     * phpshow路由处理
     * 规则只有一种
     */
    public function route()
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
        $this->ct = $path['1'] ?? $this->ct;
        $this->ac = $path['2'] ?? $this->ac;
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
    public function page_close()
    {
        $memory = memory_get_usage();
        debug::show_debug_error();
        echo "使用内存:".$this->convert($memory - $this->memory);

    }

}
$show = new show();
$show->hello();
