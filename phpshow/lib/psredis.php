<?php
/**
 * redis缓存使用
 * Created by PhpStorm.
 * User: shengsheng
 * Date: 2018/7/19
 * Time: 上午12:44
 */

namespace phpshow\lib;


class psredis
{
    public static $hand_ob = null;

    public static function handle()
    {
        if(self::$hand_ob == null)
        {
            $redis = new \Redis();
            //config文件夹读取 不连接会出现Redis server went away
            $redis->connect('127.0.0.1', 6379);
            self::$hand_ob = $redis;
        }
    }

    public static function __callStatic($name, $arguments)
    {
        self::handle();
        return self::$hand_ob->$name(implode(",",$arguments));
    }
    public function __call($name, $arguments)
    {
        self::handle();
        return self::$hand_ob->$name(implode(",",$arguments));
    }
}