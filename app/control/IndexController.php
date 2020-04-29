<?php
namespace app\control;

use app\model\UserModel;
use \phpshow\request;

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
        echo 'hello world'.lr;
        \phpshow\lib\tpl::display("index");
    }

}