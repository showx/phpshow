<?php
/**
 * 数据库配置文件
 * Author:shengsheng
 */
return [
    'mysql' => [
        'master' => [
            'host' => '172.18.0.7',
            'port' => '3306',
            'dbname' => 'phpshow',
            'username' => 'root',
            'password' => 'root',
        ],
    ],
   'postgresql' => [
       'host' => '127.0.0.1',
       'port' => '5432',
       'dbname' => 'show',
       'username' => 'postgres',
       'password' => 'postgres',
   ],
    'redis' => [
        'host' => '127.0.0.1',
        'port' => '6379',
        'auth' => '',
    ],
    'ssdb' => [
        'host' => '172.18.0.6',
        'port' => '8888',
    ],
];