<?php
namespace phpshow\lib;
/**
 * 协程redis连接池
 * Author:show
 */
class CoRedisPool
{
    /**
     * @var \Swoole\Coroutine\Channel
     */
    protected $pool;

    /**
     * RedisPool constructor.
     * @param int $size 连接池的尺寸
     */
    function __construct($size = 100)
    {
        $this->pool = new \Swoole\Coroutine\Channel($size);
        $config = \phpshow\lib\config::get("db.redis");
        for ($i = 0; $i < $size; $i++)
        {
            $redis = new \Swoole\Coroutine\Redis();
            $res = $redis->connect($config['host'], $config['port']);
            //auth
            if(!empty($config['auth']))
            {
                $redis->auth($config['auth']);
            }
            if ($res == false)
            {
                throw new RuntimeException("failed to connect redis server.");
            }
            else
            {
                $this->put($redis);
            }
        }
    }

    function put($redis)
    {
        $this->pool->push($redis);
    }

    function get()
    {
        return $this->pool->pop();
    }
}

