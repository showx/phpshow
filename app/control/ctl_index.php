<?php
namespace app\control;

use \app\lib\libtest;
/**
 * Created by PhpStorm.
 * User: showx
 * Date: 2018/8/20
 * Time: 下午4:35
 */
class ctl_index extends \phpshow\control
{
    public function index()
    {
        echo 'phpshow ';
        \phpshow\lib\tpl::display("index");
    }

}