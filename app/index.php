<?php
namespace app;
//定义工程
if(!defined("PS_APP_NAME"))
{
    define("PS_APP_NAME","app"); //须跟命名空间一致
    define("PS_APP_PATH",dirname(__FILE__));
    require dirname(PS_APP_PATH).'/vendor/autoload.php';
}
\phpshow\loader::start();