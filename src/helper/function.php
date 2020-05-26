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
 * jsondecode
 */
function jd($data)
{
    return json_decode($data,true);
}

/**
 * 解释出json转arr输出
 * @param $data
 */
function dumpJson($data)
{
    $data = jd($data);
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
    $site = \phpshow\lib\config::get("site");
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

/**
 * global漏洞的判断
 */
function killglobal()
{
    if ( ini_get('register_globals') )
    {
        if (isset($_REQUEST['GLOBALS']) || isset($_FILES['GLOBALS'])) {
            die('fuck GLOBALS');
        }
        $noUnset= array('GLOBALS','_GET','_POST','_COOKIE','_REQUEST','_SERVER','_ENV','_FILES');
        $input=array_merge($_GET,$_POST,$_COOKIE,$_SERVER,$_ENV,$_FILES,isset($_SESSION) &&is_array($_SESSION) ?$_SESSION: array());
        foreach ($input as $k=>$v) {
            if (!in_array($k,$noUnset) && isset($GLOBALS[$k])) {
                unset($GLOBALS[$k]);
            }
        }
    }
}

