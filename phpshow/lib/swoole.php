<?php
/**
 * swoole类
 * Created by PhpStorm.
 * User: pengyongsheng
 * Date: 2018/8/23
 * Time: 下午4:27
 */

namespace phpshow\lib;


class swoole
{
    //内存表
    public $table;
    public function __construct()
    {
        if(!get_extension_funcs("swoole"))
        {
            return false;
        }
    }
    /**
     * pid的获取
     */
    public function pid()
    {
        return posix_getpid();
    }

    /**
     * 创建进程
     * @param $index
     * @return \swoole_process
     */
    public function createProcess($index)
    {
        $process = new \swoole_process(function(\swoole_process $worker)use($index){
            \swoole_set_process_name(sprintf('phpshow:%s',$index));

            //具体业务逻辑

        }, false, false);
        return $process;
    }

    /**
     * 检查swoole进程
     * @param $worker
     */
    public function checkMpid($worker,$mpid)
    {
        if(!\swoole_process::kill($mpid,0)){
            $worker->exit();
        }
    }

    /**
     * 创建进程池并运行
     * @param int $workerNum
     * @param Closure $cfunction
     */
    public function runProcessPool($workerNum = 10,Closure $cfunction)
    {
        $pool = new \Swoole\Process\Pool($workerNum);
        $pool->on("WorkerStart", $cfunction);
        $pool->on("WorkerStop", function ($pool, $workerId) {
        });
        $pool->start();
    }

    /**
     * 临时表的使用
     */
    public function table()
    {
        $this->table = new \swoole_table(1024);
        $this->tableble->column('id', \swoole_table::TYPE_INT, 4);
        $this->table->column('key', \swoole_table::TYPE_STRING, 64);
        $this->table->column('value', \swoole_table::TYPE_STRING,64);
        $this->table->create();
    }

    /**
     * 序列化
     * @param mixed $data
     * @param int $flags
     * @return mixed
     */
    public function serialize(mixed $data, int $flags)
    {
        return \swoole_serialize::pack($data,$flags);
    }

    /**
     * 反序列化
     * @param string $data
     * @return mixed
     */
    public function unserialize(string $data)
    {
        return \swoole_serialize::unpack($data);
    }
}