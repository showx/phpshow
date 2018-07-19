<?php
/**
 * 输出接口
 * Author:shengsheng
 */
namespace phpshow;
class response
{
    /**
     * 输出头部信息
     */
    public function getHeader()
    {
        header('Content-Type: text/html; charset=utf-8');
    }

    /**
     * 输出json
     */
    public function json()
    {

    }

    /**
     * 输出xml
     */
    public function xml()
    {

    }

    /**
     * 设置cli输出颜色
     * @param $text
     * @param $status
     * @return string
     * @throws Exception
     */
    public function clicolor($text, $status)
    {
        $out = "";
        switch($status) {
            case "SUCCESS":
                $out = "[42m"; //Green background
                break;
            case "FAILURE":
                $out = "[41m"; //Red background
                break;
            case "WARNING":
                $out = "[43m"; //Yellow background
                break;
            case "NOTE":
                $out = "[44m"; //Blue background
                break;
            default:
                throw new Exception("Invalid status: " . $status);
        }
        return chr(27) . "$out" . "$text" . chr(27) . "[0m";
    }

    //----------------------------------------------------------
    /**
     * json to array
     */
    public function jta($data)
    {
        $data = json_decode($data,true);
        echo "====================\n";
        echo "array(\n";
        foreach($data as $key=>$val)
        {
            self::JsonToArr($val,"\$arr['$key']");
        }
        echo ")";
    }

    /**
     * json to config
     * @param $a
     */
    public function jtoc($data)
    {
        foreach($data as $key=>$val)
        {
            self::JsonToArr($val,"\$arr[$key]");
        }
    }
    /**
     * 数组配置文件式
     */
    public function configArr($arr,$e='')
    {
        if(is_array($arr))
        {
            foreach($arr as $k=>$v)
            {
                $kk = $e."['".$k."']";
                self::configArr($v,$kk);
            }
        }else{
            echo $e."=\"".$arr."\";\n";
        }
    }
    /**
     * 一行输出数组
     */
    public function lineArr($arr)
    {
        if(is_array($arr))
        {
            echo "array(";
            foreach($arr as $k=>$v)
            {
                if(!is_array($v))
                {
                    echo "\"{$k}\"=>\"{$v}\",";
                }
                self::lineArr($v);
            }
            echo "),\n";
        }
    }
    /**
     * json to array
     */
    public function JsonToArr($arr,$i=1)
    {
        $str = str_repeat(" ",$i);
        $str2 = str_repeat(" ",$i+1);
        if(is_array($arr))
        {
            echo "\n$str array(\n";
            foreach($arr as $k=>$v)
            {
                echo $str2.'"'.$k."\"=>";
                if(is_array($v))
                {
                    $j = $i+2;
                }else{
                    $j = $i;
                }
                self::garr($v,$j);
            }
            echo $str."),\n";
        }else{
            echo '"'.$arr.'",'."\n";
        }
    }

}
