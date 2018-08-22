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
    private $path = PS_APP_PATH.'/view/';
    //数据集合
    public $tpl_result = array();

    public static function init()
    {
        if(self::$instance == null)
        {
            self::$instance = new self();
        }
    }

    /**
     * 数据赋值
     * @param $key
     * @param $value
     */
    public static function assign($key,$value)
    {
        self::$tpl_result[$key] = $value;
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