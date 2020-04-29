<?php
/**
 * view模板处理
 * Created by PhpStorm.
 * User: shengsheng
 * Date: 2018/7/19
 * Time: 下午8:13
 */

namespace phpshow\lib;
use \phpshow\response;

class tpl
{
    public $instance = null;
    //数据集合
    public static $tpl_result = array('test');

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
        return PS_APP_PATH.'/view/'.$file_name.".php";
    }

    /**
     * 显示模板
     * @param $file_name
     */
    public static function display($file_name = '')
    {
        $result = self::$tpl_result;
        $closure = function($file_name) use($result){
            extract($result);
            ob_start();
            include self::include_file($file_name);
            $res = ob_get_contents();
            ob_end_clean();
            response::end($res);
        };
        $content = new class() {
            public $title = 'phpshow';
            public $result = [];
        };
        $closure = $closure->bindTo($content);
        $closure($file_name);
    }
}