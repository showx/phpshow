<?php
namespace app\moduletest\control;

/**
 * Created by PhpStorm.
 * User: shengsheng
 * Date: 2018/10/09
 * Time: 下午3:15
 */
class ctl_index extends \control
{
    public function index()
    {
        \tpl::display();
    }

}