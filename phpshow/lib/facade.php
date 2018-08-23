<?php
/**
 * 对象静态化调用
 * Created by PhpStorm.
 * User: shengsheng
 * Date: 2018/7/19
 * Time: 上午12:45
 */

namespace phpshow\lib;


class facade
{
    public static $facade_obj = null;

    /**
     * 获取句柄
     * @param $classname
     * @param $args
     * @return mixed
     */
    public static function getInstance($classname,$args){
        if(self::$facade_obj == null)
        {
            self::$facade_obj = new $classname($args);
        }
        return self::$facade_obj;
    }

    /**
     * 获取新建的类
     * @param $class
     * @return string
     */
    public static function getFacadeAccessor(){
        $classname =  static::class;
        return $classname;
    }

    public static function __callstatic($method,$arg){
        $instance=static::getInstance(static::getFacadeAccessor(),[]);
        return call_user_func_array(array($instance,$method),$arg);
    }
}