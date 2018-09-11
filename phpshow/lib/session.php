<?php
/**
 * session管理
 * Created by PhpStorm.
 * User: shengsheng
 * Date: 2018/7/19
 * Time: 下午11:45
 */

namespace phpshow\lib;


//接口参数
ini_set('session.gc_divisor', 1000);
ini_set('session.gc_probability', 1000);
//session_write_close();
session_set_save_handler(
    "\phpshow\lib\session::init",
    "\phpshow\lib\session::close",
    "\phpshow\lib\session::read",
    "\phpshow\lib\session::write",
    "\phpshow\lib\session::destroy",
    "\phpshow\lib\session::gc"
);
session_save_path( PS_RUNTIME.'/session' );
//echo 'session start';

/**
 * session接口类
 */
class session
{
    //session cookie name
    private static $session_name = '';

    //session_path
    private static $session_path = '';

    //session_id
    private static $session_id   = '';

    //session_live_time
    private static $session_live_time = 3600;

    //session类型 file || mysql
    private static $session_type = '';

    //文件缓存类句柄
    private static $fc_handler   = null;

    /**
     * 页面执行了session_start后首先调用的函数
     * @parem $save_path
     * @parem $cookie_name
     * @return void
     */
    public static function init($save_path, $cookie_name)
    {
        self::$session_name = $cookie_name;
        self::$session_path = $save_path;
        self::$session_id   = session_id();
        self::$session_live_time = empty(self::$session_live_time) ? ini_get('session.gc_maxlifetime') : self::$session_live_time;
        return true;
    }

    /**
     * 读取用户session数据
     * @parem $id
     * @return void
     */
    public static function read( $id )
    {
        $file = self::$session_path."/sess_{$id}";
        if(file_exists($file))
        {
            $tmp = (string)@file_get_contents($file);
            return $tmp;
        }else{
            return "";
        }
    }

    /**
     * 写入指定id的session数据
     * @parem $id
     * @parem $sess_data
     * @return void
     */
    public static function write($id, $sess_data)
    {
        return file_put_contents(self::$session_path."/sess_$id", $sess_data) === false ? false : true;
    }

    /**
     * 注销指定id的session
     * @parem $id
     * @return void
     */
    public static function destroy( $id )
    {
        $file = self::$session_path."/sess_$id";
        if (file_exists($file)) {
            unlink($file);
        }
        return true;
    }

    /**
     * 清理接口
     * @parem $max_lifetime
     * @return void
     */
    public static function gc($max_lifetime)
    {
        foreach (glob(self::$session_path."/sess_*") as $file) {
            if (filemtime($file) + $max_lifetime < time() && file_exists($file)) {
                unlink($file);
            }
        }
    }

    /**
     * 关闭接口（页面结束会执行）
     */
    public static function close()
    {
        return true;

    }

}