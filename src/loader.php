<?php
/**
 * phpshow核心加载
 * Author:show
 */
namespace phpshow;

//错误等级定义
error_reporting( E_ALL );
defined("PS_DEBUG") or define("PS_DEUBG","1");

define("PS_PATH",dirname(__FILE__));
//todo 不一定默认就app，要有选择
if(!defined("PS_APP_NAME"))
{
    //console取，默认使用App文件夹
    define("PS_APP_NAME","app");
    define("PS_APP_PATH",PS_PATH."/../".PS_APP_NAME);
}
define("PS_PATH2",dirname(PS_APP_PATH));
//系统级配置
define("PS_SYS_CONFIG_PATH",PS_PATH."/config/");
define("PS_CONFIG_PATH",PS_PATH2."/config/");
define("PS_HELPER_PATH",PS_PATH."/helper/");
define("PS_SYS_RUNTIME",PS_PATH."/runtime/");
define("PS_RUNTIME",PS_PATH2."/runtime/");
define("PS_LIB_PATH",PS_PATH."/lib/");

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
    //语言
    public $lang = array();
    public $ct = 'index';
    public $ac = 'index';
    public $bindings = array();
    private $loader_file = array();
    public function __construct()
    {
        $this->begin();
        spl_autoload_register(array($this, 'autoload'));
        //增加上composer加载 不采用composer内嵌的方式
        // require PS_PATH.'/composer/vendor/autoload.php';
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
     * 程序初此化
     */
    public function begin()
    {
        //不改动composer,使用这里的规则约束
        $this->autoloader = array(
            "ctl_" => "/control/",
            "mod_" => "/model/",
            "utl_" => "/utils/",
            
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
        $file = PS_PATH.DIRECTORY_SEPARATOR.str_replace('phpshow', '', $file);
    //    echo $file.lr;
        //这里加载的文件输出到debug框
        $this->loader_file[$classname] = $file;
        if (file_exists($file)) {
            require_once $file;
            return true;
        }
        $filename = basename($file);
        $filename_sub = substr($filename,0,4);

        if(defined('PS_APP_PATH'))
        {
            if(array_key_exists($filename_sub,$this->autoloader))
            {
                $filePath = PS_APP_PATH.$this->autoloader[$filename_sub].$filename;
                if(file_exists($filePath))
                {
                    require_once $filePath;
                    return true;
                }
            }
        }
        return false;
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
        $this->ct = !empty(request::item("ct"))?request::item("ct"):$this->ct;
        $this->ac = !empty(request::item("ac"))?request::item("ac"):$this->ac;
//        if(run_mode == '1')
        {
            //QUERY_STRING 参数为s
            $path = request::item("s");
            $path = explode("/",$path);
            $realpath = array();
            foreach($path as $key=>$val)
            {
                if(!empty($val))
                {
                    $realpath[] = $val;
                }
            }
            $path = $realpath;
            $exist = ['ct','ac'];
            $pathcount = count($path);
            $i = 0;
            foreach($path as $key=>$val)
            {
                if(!empty($val))
                {
                    if($i == 0)
                    {
                        $tmp = current($exist);
                        $this->$tmp = $val;
                        $i = 1;
                    }else{
                        $tmp = next($exist);
                        $this->$tmp = $val;
                    }
                }
            }
        }
        $rule_index = $this->ct."/".$this->ac;
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
        $this->module = preg_replace('/([^0-9a-z_])+/is','',$this->module);
        $this->ct = preg_replace('/([^0-9a-z_])+/is','',$this->ct);
        $this->ac = preg_replace('/([^0-9a-z_])+/is','',$this->ac);
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
     * 增加别名
     * @param array $result_me
     */
    public function addClassAlias(Array $result_me = [])
    {
        //这样处理会造成ide找不到类，还是习惯use 加载方式
        $result = [
            'util' => 'phpshow\helper\util',
            'log' => 'phpshow\lib\log',
            'acl' => 'phpshow\lib\acl',
            'api' => 'phpshow\lib\api',
            'tpl' => 'phpshow\lib\tpl',
            'db' => 'phpshow\lib\db',
            'debug' => 'phpshow\lib\debug',
            'facade' => 'phpshow\lib\facade',
            'http' => 'phpshow\lib\http',
            'psredis' => 'phpshow\lib\psredis',
            'control' => 'phpshow\control',
            'model' => 'phpshow\model',
            'request' => 'phpshow\request',
            'response' => 'phpshow\response',
            'Loader' => 'phpshow\Loader',
        ];
        if(run_mode =='1')
        {
            $result['session'] = 'phpshow\lib\session';
            //启用session
        }
        if(!empty($result_me))
        {
            $result = array_merge($result,$result_me);
        }
        foreach($result as $key=>$val)
        {
            class_alias($val, '\\'.$key);
        }
//        if(run_mode=='1')
//        {
            //用的时候才代码开启
//            session_start();
//        }
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

    public function run()
    {
        try{
            
            if(!empty($this->module))
            {
                $ctl  = PS_APP_NAME."\\".$this->module.'\control\ctl_'.$this->ct;
            }else{
                $ctl  = PS_APP_NAME.$this->module.'\control\ctl_'.$this->ct;
            }
            //强制运行在cli下的规则
            if(substr($this->ct,0,7)=='command' || substr($this->ac,0,7)=='command' )
            {
                if(run_mode!='2')
                {
                    die('run cli');
                }
            }

            if( method_exists ( $ctl, $this->ac ) === true )
            {
                $instance = new $ctl;
                $instance->{$this->ac}();
            } else {
                //客户端访问域名地址
                if(\phpshow\lib\config::get("site")['dev'] == '1')
                {
                    echo lr."ctl:".$ctl.lr;
                    echo "ac:".$this->ac.lr;
                    var_dump(request::$forms);
                    response::end("404-fucking control");
                }
                throw new \Exception('fucking control..');
            }
        }catch(\Throwable $e)
        {
            if(\phpshow\lib\config::get("site")['dev'] == '1')
            {
                lookdata($e);
            }
            //这种异常写日志
        }

    }

}

//App加载类
Class loader{

    public static $master;
    public static $result = array();
    public static function start($argc='',$argv='')
    {
        self::$master = new show();
        self::$master->addClassAlias();
        $master = self::$master;
        //swoole 肯定是run_mode等于2的
        if(run_mode=='2')
        {
            request::$forms['argc'] = $argc;
            request::$forms['argv'] = $argv;
            //可使用 module/ct/ac 这种请求方式
            //不同进行argv可能为空的情况
            if($argc>1)
            {
                if($argv['1'] == 'http' && $argv['2'] == 'start')
                {
                    swoole_set_process_name("http");
                    $host = "0.0.0.0";
                    $http = new \Swoole\Http\Server($host, 8080);
                    if(!isset($argv['3']))
                    {
                        $argv['3'] = "";
                    }
                    if($argv['3'] == '-d')
                    {
                        $daemonize = true;
                    }else{
                        $daemonize = false;
                    }
                    $http->set(array(
                        'worker_num' => 3,
                        'daemonize' => $daemonize,
                        'log_file' => PS_RUNTIME.'/swoolehttp.log'
                    ));
                    $http->on('request', function ($request, $response) use( $master ) {
                        $uri = $request->server['request_uri'];
                        //没进入这里
                        if ($uri == '/favicon.ico') {
                            $response->status(404);
                            $response->end();
                        }
                        request::init($request);
                        $master->miniroute();
                        response::setSw($response);
                        $master->run();
                    });
                    $http->start();
                    exit();
                }elseif($argv['1'] == 'cron' && $argv['2'] == 'start')
                {
                    //启动cron模式
                    $cron = new \phpshow\lib\cron();
                    $cron->start();
                    exit();
                }else{
                    request::$forms["ct"] = $argv['1'];
                    if(isset($argv['2']))
                    {
                        request::$forms["ac"] = $argv['2'];
                    }
                    if(isset($argv['3']))
                    {
                        request::$forms["command"] = $argv['3'];
                    }
                }

            }

        }
        self::$master->miniroute();
        self::$master->run();
    }
    public static function run()
    {
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
// loader::start($argc,$argv);