<?php
namespace phpshow\helper\facade;
/**
 * Created by PhpStorm.
 * User: pengyongsheng
 * Date: 2018/8/23
 * Time: 上午9:46
 */

class db extends \phpshow\lib\facade
{
    public static function getFacadeAccessor(){
        return \phpshow\lib\db::class;
    }
}