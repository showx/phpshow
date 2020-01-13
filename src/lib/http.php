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

    public static function getProxy($url,$proxy_url,$cookie="",$referer_url = "https://www.qimai.cn")
    {
        // 要访问的目标页面
//        $url = "http://baidu.com";
        // 代理服务器
//        $proxyServer = "http://ip:port";
        // 隧道身份信息
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_REFERER, $referer_url);
        // 设置代理服务器
//        curl_setopt($ch, CURLOPT_PROXYTYPE, 0); //http
        curl_setopt($ch, CURLOPT_PROXYTYPE, 5); //sock5
        curl_setopt($ch, CURLOPT_PROXY, $proxy_url);
        // 设置隧道验证信息
        curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 2.0.50727;)");
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if(!empty($cookie))
        {
            curl_setopt($ch,CURLOPT_COOKIE,$cookie);
        }
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }


    /**
     * 向指定网址发送post请求
     * @parem $url
     * @parem $query_str
     * @parem $type=''
     * @parem $timeout=5
     * @return string
     */
    public static function post($url, $query_str, $type='',$timeout=5)
    {
        $startt = time();
        if(is_array($query_str))
        {
            $query_str = http_build_query($query_str);
        }
        if( function_exists('curl_init') )
        {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERAGENT, self::$user_agent );
            if($type=='json')
            {
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json; charset=utf-8',
                ]);
            }
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $query_str);
            $result = curl_exec($ch);
//            $errno  = curl_errno($ch);
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
    public function post_file($url, $files, $fields, $timeout=30, $referer_url='')
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

    /**
     * rolling 表求数据
     * @param $urls
     */
    public static function rolling($urls)
    {
        $mh = curl_multi_init();
        foreach($urls as $key=>$url)
        {
            $ch{$key} = curl_init();
            curl_setopt($ch{$key}, CURLOPT_URL, $url);
            curl_setopt($ch{$key}, CURLOPT_HEADER, 0);
            curl_setopt($ch{$key}, CURLOPT_CONNECTTIMEOUT, 2);
            curl_setopt($ch{$key}, CURLOPT_TIMEOUT, 5);
            curl_multi_add_handle($mh,$ch{$key});
        }
        $active = null;
        do {
            $mrc = curl_multi_exec($mh, $active);
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);

        while ($active && $mrc == CURLM_OK) {
            //这一段是核心
//            while (curl_multi_exec($mh, $active) === CURLM_CALL_MULTI_PERFORM);
            curl_multi_exec($mh, $active);
            $tmp = curl_multi_select($mh);  //经常-1的原因
            if ($tmp != -1) {
                do {
                    $mrc = curl_multi_exec($mh, $active);
                } while ($mrc == CURLM_CALL_MULTI_PERFORM);
            }
        }
        foreach($urls as $key=>$val)
        {
            curl_multi_remove_handle($mh, $ch{$key});
        }
        curl_multi_close($mh);
    }

    /**
     * rolling 表求数据
     * @param $urls
     */
    public static function rolling2($urls)
    {
        if(empty($urls))
        {
            return 'empty';
        }

        $mh = curl_multi_init();
        foreach ($urls as $i => $url) {
            $conn[$i] = curl_init($url);
            curl_setopt($conn[$i], CURLOPT_RETURNTRANSFER, 1);
            curl_multi_add_handle($mh, $conn[$i]);
        }
        do {
            $status = curl_multi_exec($mh, $active);
            $info = curl_multi_info_read($mh);
            if (false !== $info) {
//                var_dump($info);
            }
        } while ($status === CURLM_CALL_MULTI_PERFORM || $active);

        foreach ($urls as $i => $url) {
            $res[$i] = curl_multi_getcontent($conn[$i]);
            curl_close($conn[$i]);
        }
        $res2 = [];
        foreach($res as $i=>$v)
        {
            $res2[$urls[$i]] = $v;
        }
        unset($res);
        return $res2;
    }
}