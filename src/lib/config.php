<?php
/**
 * config配置
 * 系统类config
 * 应用层config
 * Author:show
 */
namespace phpshow\lib;
Class config
{
    //框架配置文件
    public $config = array();
    public static $instance = null;
    public $lang;
    public static function instance()
    {
        if(self::$instance == null)
        {
            self::$instance = new self();
            self::$instance->system_config();
        }
        return self::$instance;
    }
    /**
     * 初始化配置
     * 先要引进文件
     */
    public static function include($filename='',$reload=false):void
    {
        if(!empty($filename))
        {
            if(isset(self::instance()->config[$filename]))
            {
                if($reload == false)
                {
                    return;
                }
            }
            //加载配置文件
            $file1 = PS_SYS_CONFIG_PATH.$filename.".php";
            $file2 = PS_CONFIG_PATH.$filename.".php";
            // echo "debug:".$file1.lr.$file2.lr;
            $file_exist1 = file_exists($file1);
            $file_exist2 = file_exists($file2);
            if($file_exist1)
            {
                $file_arr1 = include $file1;
            }
            if($file_exist2)
            {
                $file_arr2 = include $file2;
            }
            if($file_exist1 && $file_exist2)
            {
                self::instance()->config[$filename] = array_merge($file_arr1,$file_arr2);
            }elseif($file_exist1)
            {
                self::instance()->config[$filename] = $file_arr1;
            }elseif($file_exist2)
            {
                self::instance()->config[$filename] = $file_arr2;
            }
        }
        
    }

    /**
     * 加载系统配置
     * 配置文件的读取
     * 默认加载 phpshow config -> app config
     */
    public function system_config()
    {
        $config_arr = include PS_SYS_CONFIG_PATH.DIRECTORY_SEPARATOR.'include.php';
        foreach($config_arr as $load_key => $load_config)
        {
            $arr = include PS_SYS_CONFIG_PATH.DIRECTORY_SEPARATOR.$load_config.'.php';
            self::instance()->config[$load_key] = $arr;
        }
        //lang语言包的加载
        if(self::instance()->config['site']['lang_on'] == '1')
        {
            $lang = self::instance()->config['site']['lang_default'];
            $lang_file = PS_SYS_CONFIG_PATH.DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.$lang.'.php';
            if(file_exists($lang_file))
            {
                self::instance()->lang = include $lang_file;
            }
        }
    }

    /**
     * 获取配置
     * get 获取配置
     * get database.mysql
     * 获取database文件里的mysql数组配置
     * config::get("database.mysql");
     */
    public static function get($key)
    {
        $keys = explode(".",$key);
        $count = count($keys);
        
        if($count>1)
        {
            $config_name = array_shift($keys);
            self::instance()->include($config_name);
            $data = self::instance()->config[$config_name];
            if($data)
            {
                foreach($keys as $cckey)
                {
                    if(isset($data[$cckey]))
                    {
                        $data = $data[$cckey];
                    }else{
                        break;
                    }
                }
                return $data;
            }
        }else{
            self::instance()->include($key);
            return self::instance()->config[$key];
        }
    }
    /**
     * 递归设置数据
     */
    public static function walk_key($data,$keys,$value)
    {
        $ckey = array_shift($keys);
        if(empty($keys))
        {
            return $value;
        }else{
            $tmp = self::walk_key($data,$keys,$value);
            $data[$ckey] = $tmp;
        }
        return $data;
    }
    /**
     * 更新配置
     * 代码自动生成数据配置
     * config::set("database.mysql.dbname","phpshow");
     */
    public static function set($key,$value):void
    {
        $keys = explode(".",$key);
        $count = count($keys);
        if($count>1)
        {
            $config_name = array_shift($keys);
            $data = self::instance()->config[$config_name];
            $data = self::walk_key($data,$keys,$value);
        }else{
            $config_name = $keys;
            $data = self::instance()->config[$keys] = $value;
        }
        //只能生成在应用层config
        file_put_contents(PS_CONFIG_PATH.$config_name.".php",$data);
    }

}