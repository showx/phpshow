<?php
/**
 * Created by PhpStorm.
 * User: pengyongsheng
 * Date: 2018/8/23
 * Time: 上午9:10
 */
namespace phpshow\helper\traits;
trait instance{
    public static $instance = null;
    public static function instance()
    {
        if(self::$instance == null)
        {
            self::$instance = new self();
        }
        return self::$instance;
    }

}