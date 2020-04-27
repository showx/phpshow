<?php
namespace phpshow\lib;
/**
 * 简单池
 * 可存放mysql与redis
 * 定时更新与删除池
 * Author:show
 */
class Pool
{
    protected $pool = [];

    /**
     * MysqlPool constructor.
     * @param int $size 连接池的尺寸
     */
    function __construct($size = 20 , $type='mysql' ,$channel='')
    {
        $config = \phpshow\lib\config::get("db.mysql")['master'];
        for ($i = 0; $i < $size; $i++)
        {
            $mysql = new \phpshow\lib\mysql();
            if($mysql)
            {
                $this->put($mysql);
            }
        }
    }

    function put($mysql)
    {
        $this->pool->push($mysql);
    }

    function get()
    {
        return $this->pool->pop();
    }
}

