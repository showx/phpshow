<?php
namespace app\control;

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

        $log = new \phpshow\lib\log();
        $log->info("shjow");

        tpl::display("index");
    }
}