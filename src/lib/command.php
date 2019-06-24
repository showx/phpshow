<?php
/**
 * 命令类
 * Author:show
 */

namespace phpshow\lib;


class command
{
    private $color = [
        "none"          =>  "\033[0m",
        "black"         =>  "\033[0;30m",
        "dark_gray"     =>  "\033[1;30m",
        "blue"          =>  "\033[0;34m",
        "light_blue"    =>  "\033[1;34m",
        "green"         =>  "\033[0;32m",
        "light_green"   =>  "\033[1;32m",
        "cyan"          =>  "\033[0;36m",
        "light_cyan"    =>  "\033[1;36m",
        "red"           =>  "\033[0;31m",
        "light_red"     =>  "\033[1;31m",
        "purple"        =>  "\033[0;35m",
        "light_purple"  =>  "\033[1;35m",
        "brown"         =>  "\033[0;33m",
        "yellow"        =>  "\033[1;33m",
        "light_gray"    =>  "\033[0;37m",
        "white"         =>  "\033[1;37m",
    ];
    public function __construct()
    {

    }
    
    /**
     * 需要输出的help
     * ct|ac|"argv"
     */
    public function help()
    {
        $help_str =<<<eof
              _               _
        _ __ | |__  _ __  ___| |__   _____      __
       | '_ \| '_ \| '_ \/ __| '_ \ / _ \ \ /\ / /
       | |_) | | | | |_) \__ \ | | | (_) \ V  V /
       | .__/|_| |_| .__/|___/_| |_|\___/ \_/\_/
       |_|         |_|
       
        说明:
        1.格式 {ct} {ac} "{argv参数}"
        2.例: phpshow index test "starttime=20190301&endtime=20190315&test=1"
        3.相对应进入到index控制器的test方法，解释参数3对应的参数到控制器里调用。
        4.默认时间不填有相关处理
        5.date_type时间范围类型[1.一天 2.现十分钟内 3.本分钟 4.上一分钟 5.本小时]。

eof;
        $this->Techo("light_green",$help_str);
    }
    /**
     * 属性解释器
     * 获取相关时间
     * 跑取一天
     * 跑取十分钟之内
     * 跑取本分钟数据
     * 跑取本小时数据
     */
    public function explain()
    {
        //解释argv['3'] 相关的参数
        //$_SERVER['argv'] $argv

    }
    /**
     * 获取色值
     */
    public function getColor($color)
    {
        if(!isset($this->color[$color]))
        {
            $color = 'none';
        }
        return $this->color[$color];
    }

    /**
     * 返回色值
     */
    public function Techo($color = '',$text = '',$br = lr)
    {   
        if(!isset($this->color[$color]))
        {
            $color = 'none';
        }
        if(!empty($text))
        {
            echo $this->color[$color].$br.$text.$br.$this->color['none'];
        }
        
    }
}