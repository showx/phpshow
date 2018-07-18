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

}
