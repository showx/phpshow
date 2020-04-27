<?php
namespace app\control;

use app\model\user1Model;
use \phpshow\request;
use \phpshow\lib\redis;
/**
 * Created by PhpStorm.
 * User: showx
 * Date: 2018/8/20
 * Time: 下午4:35
 */
class IndexController extends \phpshow\control
{
    public function index($name='php',$name2='show')
    {
        \phpshow\lib\tpl::display("index");
    }

}