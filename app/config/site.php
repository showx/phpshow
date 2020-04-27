<?php
/**
 * 站点配置文件
 * Author:shengsheng
 */
return [
    //框架里的模式 [0普通启动|1 workerman模式];
    'type' => 1,
    //启动的端口
    'host' => '0.0.0.0',
    'port' => 8080,
    'count' => 4,
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