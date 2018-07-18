<?php
namespace mongox;
//当前框架没有常量配置的时候，才读取默认的
if(!defined("MONGOX_HOST"))
{
    include_once dirname(__FILE__).'/mongodb.config.php'; 
}
/**
 * mongolog日志类
 * 使用静态方法保存数据
 * @Author:show
 */
Class log{
    //服务id
    public $serverid;
    //哪个表,这个一定要给的
    public $username;
    public $password;
    //账号密码等
    public $col_id;
    private static $logs = array();

    /**
     * @desc 增加数据
     */
    public static function add($log_name, $msg)
    {
        self::$logs[ $log_name ][] = $msg;
    }

    /**
     * @desc 保存数据
     * 一般在页面关闭的时候保存
     * register_shutdown_function('handler_php_shutdown');
     */
    public static function save()
    {
        $mogo = new \mongox\mongoadapter(MONGOX_HOST,MONGOX_PORT,MONGOX_USER,MONGOX_PASS);
        $select = $mogo->select(MONGOX_DB);
        $siteid = MONGOX_ID;

        foreach(self::$logs as $log_name => $log_datas )
        {
            $msgs = '';
            foreach($log_datas as $msg) {
                $msgs .= $msg."\r\n";
            }
            $arr = array();
            $time = time();
            $day = date('Y-m-d',$time);
            $arr['logname'] = $log_name;
            $arr['day'] = $day;
            $arr['datetime'] = $time;
            $arr['log'] = $msgs; 
            //统一存进
            $mogo->insert($siteid,$arr);
            
            
        }
        self::$logs = array();

    }

}

