<?php
namespace phpshow\helper\facade;
/**
 * Created by PhpStorm.
 * User: pengyongsheng
 */

class log extends \phpshow\lib\facade
{
    public static function getFacadeAccessor(){
        return \phpshow\lib\log::class;
    }
}