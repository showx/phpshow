<?php
/**
 * 常用工具类
 * Created by PhpStorm.
 * User: shengsheng
 * Date: 2018/7/19
 * Time: 上午9:37
 */
namespace phpshow\helper;
class util
{
    public static $client_ip;
    public static $iphand;
    /**
     * 获得用户的真实IP 地址
     *
     * HTTP_X_FORWARDED_FOR 的信息可以进行伪造
     * 对于需要检测用户IP是否重复的情况，如投票程序，为了防止IP伪造
     * 可以使用 REMOTE_ADDR + HTTP_X_FORWARDED_FOR 联合使用进行杜绝用户模拟任意IP的可能性
     *
     * @param 多个用多行分开
     * @return void
     */
    public static function get_client_ip()
    {
        $client_ip = '';
        if( self::$client_ip !== NULL )
        {
            return self::$client_ip;
        }
        //分析代理IP
        if( isset($_SERVER['HTTP_X_FORWARDED_FOR2']) )
        {
            $_SERVER['HTTP_X_FORWARDED_FOR'] = $_SERVER['HTTP_X_FORWARDED_FOR2'];
        }
        if( isset($_SERVER['HTTP_X_FORWARDED_FOR']) )
        {
            $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            foreach ($arr as $ip)
            {
                $ip = trim($ip);
                if ($ip != 'unknown' ) {
                    $client_ip = $ip; break;
                }
            }
        }
        else
        {
            $client_ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
        }
        preg_match("/[\d\.]{7,15}/", $client_ip, $onlineip);
        if(empty($onlineip[0]))
        {
            if(isset(\phpshow\request::$header['x-real-ip']))
            {
                $onlineip[0] = \phpshow\request::$header['x-real-ip'];
            }
        }
        $client_ip = ! empty($onlineip[0]) ? $onlineip[0] : '0.0.0.0';
        self::$client_ip = $client_ip;
        return $client_ip;
    }

    /**
     * 生成盐
     */
    public function generateSalt($length = 12,$chars = null){
        if( empty($chars) ){
          $chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
        }
        $chars = str_shuffle($chars);
        $num = $length < strlen($chars) - 1 ? $length:strlen($chars) - 1;
        return substr($chars,0,$num);
    }

    /**
     * 解析ip地址
     */
    public static function getIPCity($ip)
    {
        if(!self::$iphand)
        {
            self::$iphand = new \phpshow\lib\ip();
        }
        $data = self::$iphand->find($ip);
        $result['Country'] = $data['0'] ?? '';
        $result['Province'] = $data['1'] ?? '';
        $result['City'] = $data['2'] ?? '';
        return $result;
    }


    /**
     * 获得当前的Url
     */
    public static function get_cururl()
    {
        if(!empty($_SERVER["REQUEST_URI"]))
        {
            $scriptName = $_SERVER["REQUEST_URI"];
            $nowurl = $scriptName;
        }
        else
        {
            $scriptName = $_SERVER["PHP_SELF"];
            $nowurl = empty($_SERVER["QUERY_STRING"]) ? $scriptName : $scriptName."?".$_SERVER["QUERY_STRING"];
        }
        return $nowurl;
    }

    /**
     * 判断是否为utf8字符串
     * @parem $str
     * @return bool
     */
    public static function is_utf8($str)
    {
        if ($str === mb_convert_encoding(mb_convert_encoding($str, "UTF-32", "UTF-8"), "UTF-8", "UTF-32"))
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * utf8编码模式的中文截取2，单字节截取模式
     * 这里不使用mbstring扩展
     * @return string
     */
    public static function utf8_substr($str, $slen, $startdd=0)
    {
        return mb_substr($str , $startdd , $slen , 'UTF-8');
    }

    /**
     * utf-8中文截取，按字数截取模式
     * @return string
     */
    public static function utf8_substr_num($str, $length, $start=0)
    {
        preg_match_all('/./su', $str, $ar);
        if( count($ar[0]) <= $length ) {
            return $str;
        }
        $tstr = '';
        $n = 1;
        for($i=0; isset($ar[0][$i]); $i++)
        {
            if($n < $length)
            {
                $tstr .= $ar[0][$i];
                /*
                if( strlen($ar[0][$i]) == 1) $n += 0.5;
                else $n++;
                */
                $n++;
            } else {
                break;
            }
        }
        return $tstr;
    }

    /**
     * 转换单位
     * @param $size
     * @return string
     */
    public static function bunit_convert($size)
    {
        $unit=array('b','kb','mb','gb','tb','pb');
        return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
    }

    /**
     * 发送邮件
     * @param $mail_to
     * @param $mail_fromname
     * @param $mail_from
     * @param $mail_subject
     * @param $mail_body
     * @param $mail_type
     * @return bool
     */
    public static function sendmail($mail_to, $mail_fromname, $mail_from, $mail_subject, $mail_body, $mail_type='')
    {
        include_once PS_PATH."/helper/phpmailer/PHPMailer.php";
        include_once PS_PATH."/helper/phpmailer/Exception.php";
        include_once PS_PATH."/helper/phpmailer/OAuth.php";
        include_once PS_PATH."/helper/phpmailer/POP3.php";
        include_once PS_PATH."/helper/phpmailer/SMTP.php";
        $mail = new \PHPMailer\PHPMailer\PHPMailer;
        $mail->SMTPDebug = 3;                               // Enable verbose debug output

        $mail->isSMTP();                                      // Set mailer to use SMTP
        $mail->Host = 'smtp.exmail.qq.com';  // Specify main and backup SMTP servers
        $mail->SMTPAuth = true;                               // Enable SMTP authentication
        $mail->Username = 'td@luabuy.com';                 // SMTP username
        $mail->Password = '123';                           // SMTP password
        $mail->SMTPSecure = 'ssl';                            // Enable TLS encryption, `ssl` also accepted
        $mail->Port = 465;                                    // TCP port to connect to

        $mail->setFrom($mail_from, $mail_fromname);
        $mail->addAddress($mail_to, 'phpshow');     // Add a recipient

        if($mail_type=='html')
        {
            $mail->isHTML(true);                                  // Set email format to HTML
        }
        $mail->Subject = $mail_subject;
        $mail->Body    = $mail_body;

        if(!$mail->send()) {
            return false;
        } else {
            return true;
        }
    }


}