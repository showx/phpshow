<?php
/**
 * request类
 * Author:shengsheng
 */
namespace phpshow;
class request
{
    //用户的cookie
    public static $cookies = array();

    //把GET、POST的变量合并一块，相当于 _REQUEST
    public static $forms = array();
    
    //_GET 变量
    public static $gets = array();

    //_POST 变量
    public static $posts = array();

    public static $header = [];

    public static $pinfos = [];

    //用户的请求模式 GET 或 POST
    public static $request_mdthod = 'GET';

    //严禁保存的文件名
    public static $filter_filename = '/\.(php|pl|sh|js)$/i';

    public static $request;

   /**
    * 初始化用户请求
    * 对于 post、get 的数据，会转到 selfforms 数组， 并删除原来数组
    * 对于 cookie 的数据，会转到 cookies 数组，但不删除原来数组
    */
    public static function init($request = null)
    {
        //每次初始化应该清空一下$form
        //用户的cookie
        self::$cookies = array();
        //把GET、POST的变量合并一块，相当于 _REQUEST
        self::$forms = array();
        //_GET 变量
        self::$gets = array();
        //_POST 变量
        self::$posts = array();

        self::$request = $request;

        $magic_quotes_gpc = ini_get('magic_quotes_gpc');
        $request_arr = [];
        if($request)
        {
            //类型为workerman
            if(empty($request->method()))
            {
                return false;
            }
            self::$header = $request->header();

            if($request->cookie())
            {
                $_COOKIE = $request->cookie();
            }
            self::$request_mdthod = $request->method();
            if($request->method() == 'GET')
            {
                $request_arr = $request->get();
            }elseif($request->method() == 'POST'){
                $request_arr = $request->post();
            }
            $requestpath = $request->path();
            if($requestpath)
            {
                $request_arr['s'] = $requestpath;
            }

            // 类型为swoole
            // if($request->cookie)
            // {
            //     $_COOKIE = $request->cookie;
            // }
            // $_SERVER2 = $request->server;
            // if( empty($_SERVER2['request_method']) ) {
            //     return false;
            // }

            // //处理post、get
            // self::$request_mdthod = '';
            // if( $_SERVER2['request_method']=='GET' ) {
            //     self::$request_mdthod = 'GET';
            //     $request_arr = $request->get;
            // } else {
            //     self::$request_mdthod = $_SERVER2['request_method'];
            //     if($request->request)
            //     {
            //         $request_arr = $request->request;
            //     }
            // }
            // //POST里的变更覆盖$_REQUEST(即是表单名与cookie同名, 表单优先)
            // if($_SERVER2['request_method']=='POST') {
            //     self::$request_mdthod = 'POST';
            //     foreach( $request->post as $k => $v) {
            //         $request_arr[$k] = $v;
            //     }
            // }

            // if($_SERVER2['request_uri'])
            // {
            //     $request_arr['s'] = $_SERVER2['request_uri'];
            // }

        }else{
            //命令行模式
            if( empty($_SERVER['REQUEST_METHOD']) ) {
                return false;
            }
            //处理post、get
            self::$request_mdthod = '';
            if( $_SERVER['REQUEST_METHOD'] == 'GET' ) {
                self::$request_mdthod = 'GET';
                $request_arr = $_GET;
            } else {
                self::$request_mdthod = $_SERVER['REQUEST_METHOD'];
                $request_arr = $_REQUEST;
            }
            //POST里的变更覆盖$_REQUEST(即是表单名与cookie同名, 表单优先)
            if($_SERVER['REQUEST_METHOD'] == 'POST') {
                self::$request_mdthod = 'POST';
                foreach( $_POST as $k => $v) {
                    $request_arr[$k] = $v;
                }
            }
        }

        unset($_POST);
        unset($_GET);
        unset($_REQUEST);
        if( count($request_arr) > 0 )
        {
            foreach($request_arr as $k => $v)
            {
                 if( preg_match('/^config/i', $k) ) {
                     throw new \Exception('request var name not alllow!');
                     exit();
                 }
                 if( !$magic_quotes_gpc ) {
                     self::add_s( $v );
                 }
                 self::$forms[$k] = $v;
                 if( self::$request_mdthod=='POST' ) {
                     self::$posts[$k] = $v;
                 } else if( self::$request_mdthod=='GET' ) {
                     self::$gets[$k] = $v;
                 }
            }
        }
        //默认ac和ct
        // self::$forms['ct'] = isset(self::$forms['ct']) ? self::$forms['ct'] : 'index';
        // self::$forms['ac'] = isset(self::$forms['ac']) ? self::$forms['ac'] : 'index';
        //处理cookie
        if( count($_COOKIE) > 0 )
        {
            if( !$magic_quotes_gpc ) {
                self::add_s( $_COOKIE );
            }
            self::$cookies = $_COOKIE;
        }
        if(isset($_SERVER['PATH_INFO']))
        {
            $path_info = $_SERVER['PATH_INFO'];
        }else{
            $path_info = "";
        }
        if(!empty($path_info))
        {
            $path_info = explode("/",trim($path_info,"/"));
            //去除ct与ac
            array_shift($path_info);
            array_shift($path_info);
            if(!empty($path_info))
            {
                $pinfo = array_chunk($path_info,2);
                self::$pinfos = [];
                foreach($pinfo as $val)
                {
                    if(count($val) == 2)
                    {
                        self::$pinfos[$val['0']] = $val['1'];
                    }
                }
            }
        }

    }
    
    //强制要求对gpc变量进行转义处理
    public static function add_s( &$array )
    {
        if( !is_array($array) )
        {
            $array =  addslashes($array);
        }
        else
        {
            foreach($array as $key => $value)
            {
                if( !is_array($value) ) {
                $array[$key] = addslashes($value);
                } else {
                self::add_s($array[$key]);
                }
            }
        }
    }

   /**
    * 获得任意表单值
    * (即相当于$_REQUEST也可能获得cookie，但是当get/post和cookie同名时，gp优先)
    */
    public static function item( $formname, $defaultvalue = '', $filter_type='')
    {
        if( isset( self::$forms[$formname] ) ) {
            return self::$forms[$formname];
        } else {
            return $defaultvalue;
        }
    }

    /**
     * json post
     */
    public static function postjson()
    {
        $tmp = false;
        if(!empty(self::$request))
        {
            foreach(self::$request->post() as $kk=>$vv)
            {
                $tmp = $kk;
                break;
            }
        }else{
            $tmp = file_get_contents("php://input");
            
        }
        if($tmp)
        {
            $tmp = json_decode($tmp,true);
        }
        return $tmp;
    }
    
   /**
    * 获得get表单值
    */
    public static function get( $formname, $defaultvalue = '', $filter_type='' )
    {
        if( isset( self::$gets[$formname] ) ) {
            return self::$gets[$formname];
        } else {
            return $defaultvalue;
        }
    }
    
   /**
    * 获得post表单值
    */
    public static function post( $formname, $defaultvalue = '', $filter_type='' )
    {
        if( isset( self::$posts[$formname] ) ) {
            return self::$posts[$formname];
        } else {
            return $defaultvalue;
        }
    }
    
   /**
    * 获得指定cookie值
    */
    public static function cookie( $key, $defaultvalue = '', $filter_type='' )
    {
        if( isset( self::$cookies[$key] ) ) {
            return self::$cookies[$key];
        } else {
            $value = $defaultvalue;
        }
        return $value;
    }

    public static function setRequest($request)
    {
        static::$request = $request;
    }
    
}
