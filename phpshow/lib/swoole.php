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
    /**
     * 序列化
     * @param mixed $data
     * @param int $flags
     * @return mixed
     */
    public function serialize(mixed $data, int $flags)
    {
        return swoole_serialize::pack($data,$flags);
    }

    /**
     * 反序列化
     * @param string $data
     * @return mixed
     */
    public function unserialize(string $data)
    {
        return swoole_serialize::unpack($data);
    }
}