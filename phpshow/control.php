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
    //是否ajax请求
    public $is_ajax = false;
    public function __construct()
    {
        $this->is_ajax = $this->is_ajax();
        self::_Acl();
    }

    /**
     * 判断权限
     */
    public function _Acl()
    {

    }

    /**
     * 判断是否ajax请求
     * @return bool
     */
    public function is_ajax()
    {
        if(PS_ISAJAX) return true;
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtoupper($_SERVER['HTTP_X_REQUESTED_WITH'])=='XMLHTTPREQUEST';
    }

    /**
     * 输出版本
     */
    final public function welcome()
    {
        echo 'phpshow';
    }
}