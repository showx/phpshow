<?php
/**
 * 定义一下加载函数
 * Created by PhpStorm.
 * User: shengsheng
 * Date: 2018/7/19
 * Time: 上午12:29
 */
/**
 * 方便输出数据
 * @param $data
 */
function dump(...$data)
{
    echo '<pre>';
    var_dump($data);
    echo '</pre>';
}

/**
 * 解释出json转arr输出
 * @param $data
 */
function dumpJson($data)
{
    $data = json_decode($data,true);
    echo '<pre>';
    var_dump($data);
    echo '</pre>';
}

/**
 * 输出所有数据
 */
function dumpall()
{
    echo '<pre>';
    var_dump(func_get_args());
    echo '</pre>';
}

/**
 * 范围输出
 * @param $data
 */
function lookdata($data)
{
    echo '<div style="background-color:#000000;color:#ffffff;width:500px;height:200px;position: relative;overflow: scroll;">';
    echo "<pre>";
    echo var_export($data,true);
    echo "</pre>";
    echo '</div>';
}
/**
 * set cookie
 */
function sCookie($key,$value)
{
    if(empty($key))
    {
        return false;
    }
    $site = \App::getConfig("site");
    $expire = 1800;
    $_COOKIE[$key] = $value;
    return setcookie($key,$value,time() + $expire,'',$site['cookie_domain']);
}

/**
 * 获取cookie
 * @param $key
 * @return mixed
 */
function gCookie($key)
{
    if(isset($_COOKIE[$key]))
    {
        return $_COOKIE[$key];
    }else{
        return '';
    }

}
/**
 * 删除cookie
 * @param $key
 * @return bool
 */
function dCookie($key)
{
    return setcookie($key,'');
}
//--------tpl相关---------
function tpl_file($file_name)
{
    $file =  \phpshow\lib\tpl::include_file($file_name);
    return $file;
}
function setlang($lang = 'zh_CN')
{
//    putenv('LANG='.$lang );
    setlocale(LC_ALL, $lang);
}
//获取语言包的文字
function getlang($key)
{
    return \phpshow\App::$master->lang[$key];
}