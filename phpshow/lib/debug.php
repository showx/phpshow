<?php
/**
 * 捕获错误信息
 * Author:show
 */
namespace phpshow\lib;
Class debug
{
    //错误类型数组
    //实际上错误句柄函数并不能处理 E_ERROR、E_PARSE、E_CORE_ERROR、E_CORE_WARNING、E_COMPILE_ERROR、 E_COMPILE_WARNING
    //下面会列出上面几种只是用作参考
    public static $_debug_errortype = array (
        E_WARNING         => "<font color='#CDA93A'>警告</font>",
        E_NOTICE          => "<font color='#CDA93A'>普通警告</font>",
        E_USER_ERROR      => "<font color='#D63107'>用户错误</font>",
        E_USER_WARNING    => "<font color='#CDA93A'>用户警告</font>",
        E_USER_NOTICE     => "<font color='#CDA93A'>用户提示</font>",
        E_STRICT          => "<font color='#D63107'>运行时错误</font>",
        E_ERROR           => "致命错误",
        E_PARSE           => "解析错误",
        E_CORE_ERROR      => "核心致命错误",
        E_CORE_WARNING    => "核心警告",
        E_COMPILE_ERROR   => "编译致命错误",
        E_COMPILE_WARNING => "编译警告"
    );

    private static $_debug_error_msg;

    /**
     * 错误接管函数
     */
    public static function handler_debug_error($errno, $errmsg, $filename, $linenum, $vars)
    {
        $err = self::debug_format_errmsg('debug', $errno, $errmsg, $filename, $linenum, $vars);
        if( $err != '@' )
        {
            self::$_debug_error_msg .= $err;
        }
    }

    /**
     * exception接管函数
     */
    public static function handler_debug_exception($e)
    {
        $errno     = $e->getCode();
        $errmsg    = $e->getMessage();
        $linenum   = $e->getLine();
        $filename  = $e->getFile();
        $backtrace = $e->getTrace();
        self::handler_debug_error($errno, $errmsg, $filename, $linenum, $backtrace);
    }

    /**
     * 格式化错误信息
     */
    public static function debug_format_errmsg($log_type='debug', $errno, $errmsg, $filename, $linenum, $vars)
    {
        $user_errors = array(E_USER_ERROR, E_USER_WARNING, E_USER_NOTICE);
        //处理从 catch 过来的错误
        if (in_array($errno, $user_errors))
        {
            foreach($vars as $k=>$e)
            {
                if( is_object($e) && method_exists($e, 'getMessage') )
                {
                    $errno     = $e->getCode();
                    $errmsg    = $errmsg.' '.$e->getMessage();
                    $linenum   = $e->getLine();
                    $filename  = $e->getFile();
                    $backtrace = $e->getTrace();
                }
            }
        }
        //生产环境不理会普通的警告错误
        $not_save_error = array(E_USER_ERROR, E_USER_WARNING, E_USER_NOTICE, E_NOTICE, E_USER_WARNING, E_WARNING);
        if( !in_array($errno, $not_save_error) )
        {
            return '@';
        }
        //读取源码指定行
        if( !is_file($filename) )
        {
            return '@';
        }
        $fp = fopen($filename, 'r');
        $n = 0;
        $error_line = '';
        while( !feof($fp) )
        {
            $line = fgets($fp, 1024);
            $n++;
            if( $n==$linenum ) {
                $error_line = trim($line);
                break;
            }
        }
        fclose($fp);
        //如果错误行用 @ 进行屏蔽，不显示错误
        if( $error_line[0]=='@' || preg_match("/[\(\t ]@/", $error_line) ) {
            return '@';
        }
        $err = '';
        if( $log_type=='debug' )
        {
            $err = "<div style='font-size:14px;line-height:160%;border-bottom:1px dashed #ccc;margin-top:8px;'>\n";
        }
        else
        {
            if( !empty($_SERVER['REQUEST_URI']) )
            {
                $scriptName = $_SERVER['REQUEST_URI'];
                $nowurl = $scriptName;
            } else {
                $scriptName = $_SERVER['PHP_SELF'];
                $nowurl = empty($_SERVER['QUERY_STRING']) ? $scriptName : $scriptName.'?'.$_SERVER['QUERY_STRING'];
            }
            //替换不安全字符
            $f_arr_s = array('<', '*', '#', '"', "'", "\\", '(');
            $f_arr_r = array('〈', '×', '＃', '“', "‘", "＼", '（');
            $nowurl = str_replace($f_arr_s, $f_arr_r, $nowurl);

            $nowtime = date('Y-m-d H:i:s');
            $err = "Time: ".$nowtime.' @URL: '.$nowurl."\n";
        }
        if( empty(self::$_debug_errortype[$errno]) )
        {
            self::$_debug_errortype[$errno] = "<font color='#466820'>手动抛出</font>";
        }
        $error_line = htmlspecialchars($error_line);
        //$err .= "<strong>PHPSHOW框架应用错误跟踪：</strong><br />\n";
        $err .= "发生环境：" . date("Y-m-d H:i:s", time()).'::' . "<br />\n";
        $err .= "错误类型：" . self::$_debug_errortype[$errno] . "<br />\n";
        $err .= "出错原因：<font color='#3F7640'>" . $errmsg . "</font><br />\n";
        $err .= "提示位置：" . $filename . " 第 {$linenum} 行<br />\n";
        $err .= "断点源码：<font color='#747267'>{$error_line}</font><br />\n";
        $err .= "详细跟踪：<br />\n";
        $backtrace = debug_backtrace();
        array_shift($backtrace);
        $narr = array('class', 'type', 'function', 'file', 'line');
        foreach($backtrace as $i => $l)
        {
            foreach($narr as $k)
            {
                if( !isset($l[$k]) ) $l[$k] = '';
            }
            $err .= "<font color='#747267'>[$i] in function {$l['class']}{$l['type']}{$l['function']} ";
            if($l['file']) $err .= " in {$l['file']} ";
            if($l['line']) $err .= " on line {$l['line']} ";
            $err .= "</font><br />\n";
        }
        $err .= $log_type=='debug' ? "</div>\n" : "------------------------------------------\n";
        return $err;
    }


    /**
     * 显示调试信息（程序结束时执行）
     * 仅在 handler_php_shutdown 里调用
     * 只用于页面显示
     */
    public static function show_debug_error()
    {
        if( self::$_debug_error_msg != '' )
        {
            if( 1 )
            {
                $js  = '<script language=\'javascript\'>';
                $js .= 'function debug_close_all() {';
                $js .= '    document.getElementById(\'debug_ctl\').style.display=\'none\';';
                $js .= '    document.getElementById(\'debug_errdiv\').style.display=\'none\';';
                $js .= '}</script>';
                echo $js;
                echo '<div id="debug_ctl" style="width:100px;line-height:24px;position:absolute;top:5px;left:800px;border:1px solid #ccc; background: #FBFDE3; padding:2px;text-align:center">'."\n";
                echo '<a href="#" target="_self" onclick="javascript:document.getElementById(\'debug_errdiv\').style.display=\'block\';" style="font-size:12px;">[打开调试信息]</a>'."\n";
                echo '</div>'."\n";
                echo '<div id="debug_errdiv" style="width:80%;position:absolute;top:10px;left:8px;border:2px solid #ccc; background: #fff; padding:8px;display:none">';
                echo '<div style="line-height:24px; background: #FBFEEF;;"><div style="float:left"><strong>PHPSHOW框架应用错误/警告信息追踪：</strong></div><div style="float:right"><a href="#" onclick="javascript:debug_close_all();" target="_self">[关闭全部]</a></div>';
                echo '<br style="clear:both"/></div>';
                echo self::$_debug_error_msg;
                echo '<br style="clear:both"/></div>';
            }
            else
            {
                //日志只保留当天的, 以免写程序不严谨造成太多错误日志没用时清理
                $data_name = date("Ymd", time());
                $data_name_old = date("Ymd", time() - 86400);
                $error_log =   dirname(__FILE__).'/runtime/log/php_error'.$data_name.'.log';
                $fname_old =   dirname(__FILE__).'/runtime/log/php_error'.$data_name_old.'.log';

                $logmsg = preg_replace("/<font([^>]*)>|<\/font>|<\/div>|<\/strong>|<strong>|<br \/>/iU", '', self::$_debug_error_msg);
                $logmsg = preg_replace("/<div style='font-size:14px([^>]*)>/iU", "-----------------------------------------------\n错误跟踪：", $logmsg);

                //写入日志
                $fp = fopen($error_log, 'a');
                fwrite($fp, $logmsg);
                fclose($fp);

                //删除前一天日志
                if( file_exists($fname_old) ) {
                    unlink( $fname_old );
                }
            }
        }
    }


}












