<?php
/**
 * 站点配置文件
 * Author:shengsheng
 */
return [
    //框架里的模式 [0普通启动|1 workerman模式];
    'type' => 1,
    //绑定的主机地址
    'host' => '0.0.0.0',
    //启动的端口
    'port' => 8080,
    //进程数
    'count' => 4,
    //开启计划任务 [0关闭|1开启]
    'cronjob' => 0,
    //数据库池的数量
    'mysql_pool_num' => 6,
    'session_dir' => 'session',
    //cookie
    'cookie_domain' => '',
    //cookie加密
    'cookie_pwd' => 'oSEx@uuw!ppr',
    //调试模式
    'debug' => 1,
    //开发模式 [dev 查看加载异常|dev2 查看接口使用内存等]
    'dev' => 1,
    'dev2' => 0,
];