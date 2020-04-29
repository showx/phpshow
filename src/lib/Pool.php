<?php
namespace phpshow\lib;
/**
 * 简单的连接池实现
 * 类型 mysql|redis
 * 定时更新与释放
 * Author:show
 */
class Pool
{
    protected $pool;
    public $type;
    /**
     * MysqlPool constructor.
     * @param int $size 连接池的尺寸
     */
    function __construct($size = 20 , $type='mysql')
    {
        $this->type == $type;
        $this->pool = new Class{
            private $stack = [];
            private $top = -1;
            /**
             * 入栈
             */
            public function push($data)
            {
                $this->top = ++$this->top;
                $this->stack[$this->top] = $data;
            }
            /**
             * 出栈
             */
            public function pop()
            {
                if($this->top == -1){
                    return false;
                }
                $tmp = $this->stack[$this->top];
                $this->top = --$this->top;
                return $tmp;

            }
        };

        for ($i = 0; $i < $size; $i++)
        {
            if($type == 'mysql')
            {
                $middle = new \phpshow\lib\mysql();
            }elseif($type == 'redis')
            {
                $middle = new \phpshow\lib\redis();
            }
            if($middle)
            {
                $this->put($middle);
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

