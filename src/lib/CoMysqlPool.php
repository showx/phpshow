<?php
namespace phpshow\lib;
/**
 * mysql协程连接池
 * 基于swoole
 * Author:show
 */
class CoMysqlPool
{
    /**
     * @var \Swoole\Coroutine\Channel
     */
    protected $pool;

    /**
     * MysqlPool constructor.
     * @param int $size 连接池的尺寸
     */
    function __construct($size = 20)
    {
        $this->pool = new \Swoole\Coroutine\Channel($size);
        $config = \phpshow\lib\config::get("db.mysql")['master'];
        for ($i = 0; $i < $size; $i++)
        {
            $mysql = new \Swoole\Coroutine\Mysql();
            $mysql_config = [
                'host' => $config['host'],
                'port' => $config['port'],
                'user' => $config['username'],
                'password' => $config['password'],
                'database' => $config['dbname']
            ];
            $res = $mysql->connect($mysql_config);
            if ($res == false)
            {
                throw new \RuntimeException("failed to connect mysql server.");
            }
            else
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

