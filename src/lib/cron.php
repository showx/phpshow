<?php
/**
 * Created by PhpStorm.
 * User: pengyongsheng
 * Date: 2019/1/17
 * Time: 4:02 PM
 */

namespace phpshow\lib;

class cron
{
    //当前时间
    public $cur_time;
    //开始时间
    public $start_time;
    //结束时间
    public $end_time;
    //使用时间
    public $use_time;
    //单job最长运行时间
    //队列超时时间（超时多长时间允许执行队列中下一个程序）10分钟
    public $que_maxtime = 600;
    //运行目录不更改
    private $cron_path;
    //队列的文件夹
    private $cron_lock_path;
    //配置项
    private $config;
    //week :0 (for Sunday) through 6 (for Saturday)
    private $ftime = array("second"=>"s","min"=>"i","hour"=>"H","day"=>"d","week"=>"w","month"=>"m");
    //平均算法  例：分 */2 每两秒运行一次 填写不能超过60秒  
    private $ftime_dur = array("second"=>"60","min"=>"60","hour"=>"24","day"=>"1","week"=>"7","month"=>"30");
    private $runfile = [];
    private $commander;
    public function __construct()
    {
        $this->commander = new \phpshow\lib\console();
        $this->cron_path = PS_RUNTIME."/crond";
        $this->cron_lock_path = PS_RUNTIME."/log/crond_queue";
        // //只允许命令行模式运行这个脚本
        if( PHP_SAPI != 'cli' )
        {
            exit("Don't fuck!");
        }
    }

    /**
     * @desc 加载配置文件
     * 每次load时间都不一样
     */
    public function loadConfig()
    {
        $this->cur_time = time();
        //快速循环
        foreach($this->ftime as $fkey=>$fval)
        {
            $this->ftime[$fkey] = date($fval,$this->cur_time);
        }
        //周末转换为对应的数字7
        if($this->ftime['week'] == "0")
        {
            $this->ftime['week'] == "7";
        }
        //显示当前执行时间
        // $this->commander->help();
        $this->commander->Techo("green","===========crond:".date('Y-m-d H:i:s',$this->cur_time)."\n");
        $cronConfig = config::get("cron");
        $this->runfile = [];
        foreach($cronConfig as $ykey=>$yval)
        {
            $kkey = explode(" ",$ykey);
            //检查是否需要运行的文件
            $run = true;
            $i=0;
            foreach($this->ftime as $fkey=>$fval)
            {
                if($kkey[$i] == '*')
                {
                    //*任何时间检查下一项
                    continue;
                }elseif($kkey[$i] != $fval)
                {
                    $run = false;
                }
                $i++;
            }
            if($run)
            {
                //同一时间需要执行的逻辑
                $this->runfile[] = $yval;
            }
        }
    }

    /**
     * @desc 开始处理cron
    */
    public function start()
    {
        $this->loadConfig();
        foreach($this->runfile as $key=>$val)
        {
            foreach($val as $kk=>$vv)
            {
                $this->run($vv);
            }
        }
        // //完全执行完所有队列
        $this->cur_time = time();
        $this->commander->Techo("green","[ALL FINISH]".date("Y-m-d H:i",$this->cur_time)."\r\n");
    }

    /**
     * @desc 运行文件,可考虑使用fork一个进程来执行
     * @param target_file 要运行的文件
     */
    public function run($target_file)
    {

        $target_file = explode("/",$target_file);
        $this->cur_time = time();
        $ac = $target_file['1'];
        if(!isset(\phpshow\loader::$master->bindings[$target_file['0']]))
        {
            $ctl = '';
            $ctl1  = PS_APP_NAME."\\control\\".ucfirst($target_file['0']).'Controller';
            if(class_exists($ctl1))
            {
                $ctl = $ctl1;
            }
            if(!empty($ctl))
            {
                $ctl = new $ctl;
                \phpshow\loader::$master->bind($target_file['0'],$ctl);
            }
        }
        $ctl = \phpshow\loader::$master->make($target_file['0']);
        //强制运行在cli下的规则
        if( method_exists ( $ctl, $target_file['1'] ) === true )
        {
            call_user_func_array([$ctl,$ac],[]);
        }else{
            $this->commander->Techo("green",date('Y-m-d H:i ', $this->cur_time), "[MISS {$target_file['0']}/{$target_file['1']} ]", "\r\n");
        }
    }

    /**
     * 新式配置
     */
    public function new_format()
    {
        /*
    星号（*）：代表所有可能的值，例如month字段如果是星号，则表示在满足其它字段的制约条件后每月都执行该命令操作。
    逗号（,）：可以用逗号隔开的值指定一个列表范围，例如，“1,2,5,7,8,9”
    中杠（-）：可以用整数之间的中杠表示一个整数范围，例如“2-6”表示“2,3,4,5,6”
    正斜线（/）：可以用正斜线指定时间的间隔频率，例如“0-23/2”表示每两小时执行一次。同时正斜线可以和星号一起使用.
         */
        //这里只使用 * 模式 
        $the_format = array(
            "second"=>array_merge(array("*"),range("0","59")),
            "min"=>array_merge(array("*"),range("0","59")),
            "hour"=>array_merge(array("*"),range("0","23")),
            "day"=>array_merge(array("*"),range("1","31")),
            "month"=>array_merge(array("*"),range("1","12")),
            "week"=>array_merge(array("*"),range("1","7")),
        );
        return $the_format;
    }

    /**
     * 旧式的配置
     */
    public function old_format()
    {
        $the_format = array(
                '*',        //每分钟
                '*:i',      //每小时 某分
                'H:i',      //每天 某时:某分
                '@-w H:i',  //每周-某天 某时:某分  0=周日
                '*-d H:i',  //每月-某天 某时:某分
                'm-d H:i',  //某月-某日 某时-某分
                'Y-m-d H:i',//某年-某月-某日 某时-某分
        );
    }
}