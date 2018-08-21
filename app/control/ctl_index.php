<?php
use \phpshow\control;
use \phpshow\lib\db;
use \phpshow\lib\tpl;
/**
 * Created by PhpStorm.
 * User: pengyongsheng
 * Date: 2018/8/20
 * Time: 下午4:35
 */
class ctl_index extends control
{
    public function index()
    {
        echo 'hello world!';
//        $db = \phpshow\App::getC("db");
//        $result = $db->get_all("select * from test");
//        var_dump($result);

        tpl::display("index");
    }
}