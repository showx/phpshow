<?php
/**
 * control基类
 * Created by PhpStorm.
 * User: shengsheng
 * Date: 2018/7/19
 * Time: 上午12:37
 */

namespace phpshow;


class control
{
    public function __construct()
    {
        self::_Acl();
    }

    /**
     * 判断权限
     */
    public function _Acl()
    {

    }

    /**
     * 输出版本
     */
    final public function welcome()
    {
        echo 'phpshow';
    }
}