<?php
/**
 * Created by PhpStorm.
 * User: pengyongsheng
 * Date: 2019/1/17
 * Time: 4:02 PM
 */

namespace phpshow\lib;
// //超过执行时间，关闭执行的cli
ini_set('max_execution_time',0);
//执行文本，不让超时
set_time_limit(0);

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
    private $runfile = array();
    private $commander;
    public function __construct()
    {
        $this->commander = new \phpshow\lib\command();
        $this->cron_path = PS_SYS_RUNTIME."/crond";
        $this->cron_lock_path = PS_SYS_RUNTIME."/log/crond_queue";
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
        $this->commander->help();
        $this->commander->Techo("green","===========crond:".date('Y-m-d H:i:s',$this->cur_time)."\n");
        exit();
        //config::get
        $yaml = config::get("cron");

        foreach($yaml as $ykey=>$yval)
        {
            //检查是否需要运行的文件
            $run = true;
            foreach($this->ftime as $fkey=>$fval)
            {
                if($yval[$fkey] == '*')
                {
                    //*任何时间检查下一项
                    continue;
                }elseif($yval[$fkey] != $fval)
                {
                    $run = false;
                }
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
        // $pid = pcntl_fork();
        // if( $pid < 0 ){
        //   exit('fork error.');
        // } else if( $pid > 0 ) {
        //   // 主进程退出
        //   exit();
        // }
        // if( !posix_setsid() ){
        //     exit('setsid error.');
        // }
        // $pid = pcntl_fork();
        // if( $pid  < 0 ){
        // exit('fork error');
        // } else if( $pid > 0 ) {
        // // 主进程退出
        // exit;
        // }
        // cli_set_process_title('phpshow_cron');
        // //这里写个while
        // while(true)
        // {
        //     pcntl_signal_dispatch();
        //     sleep( 1 );
        //     echo "phpshow_cron".date("Y-m-d H:i:s",time()).PHP_EOL;
        // }




        $this->loadConfig();
        // //运行
        // if($this->runfile)
        // {
        //     foreach($this->runfile as $rkey=>$rval)
        //     {
        //         $rval['file'] = str_replace("/","+",$rval['file']);
        //         //生成lock_file
        //         if(!file_exists($this->cron_lock_path."/".$rval['file']))
        //         {
        //             file_put_contents($this->cron_lock_path."/".$rval['file'],"1");
        //         }

        //     }
        // }
        // //运行lock文件

        // //filectime fileatime filemtime
        // $lockfile = $this->list_file($this->cron_lock_path);
        // foreach($lockfile as $lkey=>$lval)
        // {
        //     $ctime = filectime($this->cron_lock_path."/".$lval);
        //     //超过五分钟的可疑。。要特殊处理
        //     $lockname = str_replace(".php","_runlock",$lval);
        //     $islock = file_exists($this->cron_lock_path."/".$lockname);
        //     //有运行过的要人工查日志处理,超时可能运行过逻辑，再运行就有问题。
        //     if($ctime+1200 < time() && $islock)
        //     {
        //         echo "[Warning]file:".$lval."\r\n";
        //         continue;
        //     }
        //     //没锁代表没在运行了
        //     if(!$islock)
        //     {
        //         file_put_contents($this->cron_lock_path."/".$lockname,"lock");
        //         $this->run($lval);
        //         //删除锁
        //         unlink($this->cron_lock_path."/".$lval);
        //         unlink($this->cron_lock_path."/".$lockname);
        //     }
        // }

        // //完全执行完所有队列
        $this->cur_time = time();
        echo "[ALL FINISH]".date("Y-m-d H:i",$this->cur_time)."\r\n";
    }
    /**
     * @desc 运行文件,可考虑使用fork一个进程来执行
     * @param target_file 要运行的文件
     */
    public function run($target_file)
    {
        $target_file = str_replace("+","/",$target_file);
        $this->cur_time = time();
        //运行目标文件 并输出日志
        if( file_exists($this->cron_path.'/'.$target_file) )
        {
            //输出开始时间信息
            echo "======================".date('Y-m-d H:i ', $this->cur_time), "[START {$target_file} ]", "\r\n";
            //运行
            $this->start_time = microtime(true);
            include $this->cron_path.'/'.$target_file;
            $this->end_time = microtime(true);
            $use_time = $this->end_time - $this->start_time;
            //输出结束时间信息;
            echo "\r\n";
            echo "[FINISH {$target_file} ]","USE:",$use_time.")\r\n";
        }
        else
        {
            //输出错误信息
            echo date('Y-m-d H:i ', $this->cur_time), "[MISS {$target_file} ]", "\r\n";
        }
    }

    /**
     * 列出目标文件夹里的文件
     * @param 对应的文件夹
     */
    public function list_file($path = '')
    {
        $files = dir( $path );
        $phpfile = array();
        while( $file_name = $files->read() )
        {
            //php的执行
            if( !preg_match('/\.php/', $file_name) )
            {
                continue;
            }
            $phpfile[] = $file_name;

        }
        return $phpfile;

    }
    /**
     * 运行目标文件
     */
    function run_target_file( $target_file = '' )
    {
        echo "----------------------------------------------------------------------------------------------------\r\n";
        if( file_exists($this->cron_path.'/'.$target_file) )
        {
            //输出开始时间信息
            echo date('Y-m-d H:i ', $this->cur_time), "[START {$target_file} ]", "\r\n";
            //运行
            include $this->cron_path.'/'.$target_file;
            $use_time = microtime(true) - $this->start_time;
            echo date('Y-m-d H:i ', $finish_time), "[FINISH {$target_file} ]", " USE: ", $use_time , " (", date('H:i', $start_time), "->", date('H:i', $finish_time), ")\r\n";
        }
        else
        {
            //输出错误信息
            echo date('Y-m-d H:i ', time()), "[缺少 {$target_file} 文件]", "\r\n";
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