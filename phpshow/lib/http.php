<?php
/**
 * http请求类
 * Created by PhpStorm.
 * User: shengsheng
 * Date: 2018/7/19
 * Time: 下午7:28
 */

namespace phpshow\lib;


class http
{
    public static $user_agent = 'Mozilla/5.0 (Windows; U; Windows NT 5.2; zh-CN; rv:1.9.2.13) Gecko/20101203 Firefox/3.6.13';
    /**
     * http get函数
     * @parem $url
     * @parem $$timeout=30
     * @parem $referer_url=''
     */
    public static function get($url, $timeout=30, $referer_url='')
    {
        $startt = time();
        if (function_exists('curl_init'))
        {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            if( $referer_url != '' )  curl_setopt($ch, CURLOPT_REFERER, $referer_url);
            curl_setopt($ch, CURLOPT_USERAGENT, self::$user_agent);
            $result = curl_exec($ch);
            $errno  = curl_errno($ch);
            curl_close($ch);
            return $result;
        }
        else
        {
            $Referer = ($referer_url=='' ?  '' : "Referer:{$referer_url}\r\n");
            $context =
                array('http' =>
                    array('method' => 'GET',
                        'header' => 'User-Agent:'.self::$user_agent."\r\n".$Referer
                    )
                );
            $contextid = stream_context_create($context);
            $sock = fopen($url, 'r', false, $contextid);
            stream_set_timeout($sock, $timeout);
            if($sock)
            {
                $result = '';
                while (!feof($sock)) {
                    //$result .= stream_get_line($sock, 10240, "\n");
                    $result .= fgets($sock, 4096);
                    if( time() - $startt > $timeout ) {
                        return '';
                    }
                }
                fclose($sock);
            }
        }
        return $result;
    }

    /**
     * 向指定网址发送post请求
     * @parem $url
     * @parem $query_str
     * @parem $$timeout=30
     * @parem $referer_url=''
     * @return string
     */
    public static function post($url, $query_str, $timeout=30, $referer_url='')
    {
        $startt = time();
        if( function_exists('curl_init') )
        {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $query_str);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERAGENT, self::$user_agent );
            $result = curl_exec($ch);
            $errno  = curl_errno($ch);
            curl_close($ch);
            //echo " $url & $query_str <hr /> $errno , $result ";
            return $result;
        }
        else
        {
            $context =
                array('http' =>
                    array('method' => 'POST',
                        'header' => 'Content-type: application/x-www-form-urlencoded'."\r\n".
                            'User-Agent: '.self::$user_agent."\r\n".
                            'Content-length: ' . strlen($query_str),
                        'content' => $query_str));
            $contextid = stream_context_create($context);
            $sock = fopen($url, 'r', false, $contextid);
            if ($sock)
            {
                $result = '';
                while (!feof($sock))
                {
                    $result .= fgets($sock, 4096);
                    if( time() - $startt > $timeout ) {
                        return '';
                    }
                }
                fclose($sock);
            }
        }
        return $result;
    }

    /**
     * 向指定网址post文件
     * @parem $url
     * @parem $files  文件数组 array('fieldname' => filepathname ...)
     * @param $fields 附加的数组  array('fieldname' => content ...)
     * @parem $$timeout=30
     * @parem $referer_url=''
     * @return string
     */
    public function http_post_file($url, $files, $fields, $timeout=30, $referer_url='')
    {
        $startt = time();
        if( function_exists('curl_init') )
        {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_USERAGENT, self::$user_agent );
            $need_class = class_exists('\CURLFile') ? true : false;
            foreach($files as $k => $v)
            {
                if ( $need_class ) {
                    $fields[$k] = new CURLFile(realpath($v));
                } else {
                    $fields[$k] = '@' . realpath($v);
                }
            }
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            curl_close($ch);
            return $result;
        }
        else
        {
            return false;
        }
    }
}