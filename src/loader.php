<?php
/**
 * phpshow核心加载
 * Author:show
 */
namespace phpshow;

date_default_timezone_set('Asia/Shanghai');
//错误等级定义,这里hander一下
error_reporting( E_ALL );

define("PS_PATH",dirname(__FILE__));
if(file_exists(PS_APP_PATH."/config_dev/"))
{
    define("PS_CONFIG_PATH",PS_APP_PATH."/config_dev/");
}else{
    define("PS_CONFIG_PATH",PS_APP_PATH."/config/");
}
define("PS_RUNTIME",PS_APP_PATH."/runtime/");

if( php_sapi_name() == 'cli' )
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
Class show{
    //框架开始时间
    private $starttime;
    //框架使用内存
    public $memory = 0;
    public $ct = 'index';
    public $ac = 'index';
    public $bindings = [];
    public $args = [];
    public $config = [];
    public static $count = 0;
    public $max_request = 10000;
    //常用mime
    public $mime = [
        'ico' => 'image/x-icon',
        'xml' => 'text/xml',
        'css' => 'text/css',
        'html' => 'text/html',
        'htm' => 'text/html',
        'gif' => 'image/gif',
        'jpeg' => 'image/jpeg',
        'jpg' => 'image/jpg',
        'js' => 'application/javascript',
        'png' => 'image/png',
        'webp' => 'image/webp',
        'pdf' => 'application/pdf',
        'zip' => 'application/zip',
        'ttf' => 'font/ttf',
    ];
    public function __construct()
    {
        $this->begin();
        $this->config = \phpshow\lib\config::get("site");
        //发生异常的记录
        set_exception_handler(array('\phpshow\lib\debug','handler_debug_exception'));
        if(PHP_SAPI != 'cli')
        {
            //发生错误的记录
            set_error_handler(array('\phpshow\lib\debug','handler_debug_error'), E_ALL);
            //页面结束调用 , type为1的时候作用不大
            register_shutdown_function(array($this, 'end'));
        }
    }

    public function init()
    {
        request::init();
    }

    /**
     * 程序初始化
     */
    public function begin()
    {
        $this->starttime = microtime(true);
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
        if($this->config['dev2'] == 1 && PS_ISAJAX=='0')
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
        //QUERY_STRING 参数为s
        $path = request::item("s");
        if(empty($path) && !empty($_SERVER['PATH_INFO']))
        {
            $path = $_SERVER['PATH_INFO'];
        }
        if(empty($path))
        {
            global $argv;
            if(isset($argv['1']) && isset($argv['2']))
            {
                if($argv['1']!='start' )
                {
                    $path = "/{$argv['1']}/{$argv['2']}";
                }
            }
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
    public function run($connection = null, $request = null)
    {
        if(empty($connection))
        {
            $connection = null;
        }
        if(empty($request))
        {
            $request = null;
        }
        static $request_count = 0;
        if($this->config['type'] == 1)
        {
            // 避免内存泄露，重启一下
            if(++$request_count >= $this->max_request)
            {
                \Workerman\Worker::stopAll();
            }
            // echo $request_count.lr;
            \phpshow\response::setConnection($connection);
            $localfile = PS_APP_PATH."/public".$request->path();
            // start 判断是否静态资源
            if (is_file($localfile)) {
    
                $file_info = pathinfo($localfile);
                $extension = $file_info['extension'] ?? '';
                $mimeType = $this->mime[$extension] ?? '';
                if(empty($mimeType))
                {
                    $connection->send(new \Workerman\Protocols\Http\Response(404, [], "404!!!"));
                    return true;
                }
                ob_start();
                readfile($localfile);
                $content = ob_get_clean();
                $response = new \Workerman\Protocols\Http\Response(200, [
                    // 'Content-Type' => 'text/html',
                    'Content-Type' => $mimeType,
                    'Content-Length' => filesize($localfile)
                ], $content);
    
                $connection->send($response);
                return true;
            }
            // end 静态资源加载
            request::init($request);
            $this->miniroute();
        }
        // echo "ct:".\phpshow\loader::$master->ct.lr;
        // echo "ac:".\phpshow\loader::$master->ac.lr;
        
        \phpshow\lib\tpl::$tpl_result = [];
        try{
            if(!isset($this->bindings[$this->ct]))
            {
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
                $ctl = new $ctl;
                $this->bind($this->ct,$ctl);
            }
            $ctl = $this->make($this->ct);
            //强制运行在cli下的规则
            if( method_exists ( $ctl, $this->ac ) === true )
            {
                //新建控制器
                $newctl = new $ctl;
                // echo 'ct make'.lr;
                $ctlRef = new \ReflectionClass($newctl);
                $acRef = $ctlRef->getMethod($this->ac);
                $acParams = $acRef->getParameters();
                $argsParam = [];
                if($acParams)
                {
                    foreach($acParams as $acParam)
                    {
                        $acParamClass = $acParam->getClass();
                        if(!empty($acParamClass))
                        {
                            // 从容器里获取，没有即new 
                            $paramClassName = $acParamClass->name;
                            $argsParam[] = new $paramClassName;
                        }else{
                            $argsParam[] = array_shift($this->args);
                        }
                    }
                }
                call_user_func_array(array($newctl, $this->ac), $argsParam );
                return true;
            } else {
                throw new \Exception('fucking control..');
            }
        }catch(\Throwable $e)
        {
            if($this->config['dev'] == '1')
            {
                echo "error:".$e->getMessage()." ".$this->ct."|".$this->ac.lr;
                echo "error_file_line".$e->getLine().lr;
                echo "error_file:".$e->getFile().lr;
                // lookdata($e);
            }
            // $connection->send("- -!!");
            if($this->config['type'] == '1')
            {
                $connection->send(new \Workerman\Protocols\Http\Response(404, [], "404!!!"));
            }
            return false;
        }
    }
}

//App加载类
Class loader{
    public static $master;
    public static $result = array();
    
    /**
     * 开始运行框架
     */
    public static function start()
    {
        self::$master = new show();
        $frameconfig = self::$master->config;
        $serviceHost = $frameconfig['host'] ?? '0.0.0.0';
        $servicePort = $frameconfig['port'] ?? 8080;
        $serverWorkerCount = $frameconfig['count'] ?? 1;
        if($frameconfig['type'] == '0')
        {
            self::$master->init();
            self::$master->miniroute();
            self::$master->run();
            exit();
        }
        //每次运行框架的日期
        $nowdate = date("Ymd",time());
        \Workerman\Worker::$stdoutFile = PS_RUNTIME."/log/warning".$nowdate.".log";
        if($frameconfig['cronjob'] == 1)
        {
            $cronWorker = new \Workerman\Worker();
            $cronWorker->count = 1;
            $cronWorker->onWorkerStart = function($worker)
            {
                $cron = new \phpshow\lib\cron();
                // if($worker->id === 0)
                {
                    //这里每秒检查一下crond的配置
                    \Workerman\Timer::add(1, function() use ($cron){
                        $cron->start();
                    });
                }
            };
        }
        $worker = new \Workerman\Worker("http://{$serviceHost}:{$servicePort}");
        // php进程用户
        $worker->user = 'www-data';
        $worker->count = $serverWorkerCount;
        $worker->onMessage = array(self::$master, 'run');
        \Workerman\Worker::runAll();

    }

    /**
     * 设置集合
     * setCollection
     */
    public static function setC(string $collection_name,\Closure $collection_obj)
    {
        self::$master->bind($collection_name,$collection_obj);
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