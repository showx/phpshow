<?php
/**
 * redis缓存使用
 * Created by PhpStorm.
 * User: shengsheng
 * Date: 2018/7/19
 * Time: 上午12:44
 */

namespace phpshow\lib;


class redis
{
    public static $hand_ob = null;

    public static function handle()
    {
        if(self::$hand_ob == null)
        {
            self::renew();
        }
    }

    public static function renew()
    {
        $redis = new \Redis();
        //config文件夹读取 不连接会出现Redis server went away
        $config = \phpshow\lib\config::get("db")['redis'];
        $redis->connect($config['host'], $config['port']);
        if(!empty($config['auth']))
        {
            $redis->auth($config['auth']);
        }
        if(isset($config['db']))
        {
            $redis->select($config['db']);
        }
        self::$hand_ob = $redis;
    }

    /**
     * 获取句柄
     */
    public function getHand()
    {
        self::handle();
        return self::$hand_ob;
    }

    /**
     * 静态调用方法
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        self::handle();
        return call_user_func_array(array(self::$hand_ob,$name),$arguments);
    }

    /**
     * 调用方法
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        self::handle();
        return call_user_func_array(array(self::$hand_ob,$name),$arguments);
    }
}