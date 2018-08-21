<?php
/**
 * view模板处理
 * Created by PhpStorm.
 * User: shengsheng
 * Date: 2018/7/19
 * Time: 下午8:13
 */

namespace phpshow\lib;


class tpl
{
    public $instance = null;
    //路径
    private $path = "";

    public static function init()
    {
        if(self::$instance == null)
        {
            self::$instance = new self();
        }
    }

    /**
     * 加载所需文件
     */
    public static function include_file($file_name)
    {
        include_once PS_APP_PATH.'/view/'.$file_name.".php";
    }

    /**
     * 显示模板
     * @param $file_name
     */
    public static function display($file_name)
    {
        include_once PS_APP_PATH.'/view/'.$file_name.".php";
    }
}