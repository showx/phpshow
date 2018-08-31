<?php
/**
 * 数据库配置文件
 * Author:shengsheng
 */
return [
    'mysql' => [
        'master' => [
            'host' => '127.0.0.1',
            'port' => '3306',
            'dbname' => 'show',
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
];