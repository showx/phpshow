<?php
/**
 * 数据库配置文件
 * Author:shengsheng
 */
return [
    'mysql' => [
        //相当于default
        'master' => [
            'host' => '172.20.0.7',
            'port' => '3306',
            'dbname' => 'phpshow',
            'username' => 'root',
            'password' => 'root',
        ],
        'slstat' => [
            'host' => '172.20.0.7',
            'port' => '3306',
            'dbname' => 'slstat',
            'username' => 'root',
            'password' => 'root',
        ],
        'gdslog' => [
            'host' => '172.20.0.7',
            'port' => '3306',
            'dbname' => 'gds_silang0119',
            'username' => 'root',
            'password' => 'root',
        ],
        'idata' => [
            'host' => '172.20.0.7',
            'port' => '3306',
            'dbname' => 'silanggame_idata_db001',
            'username' => 'root',
            'password' => 'root',
        ],
        'sdk' => [
            'host' => '172.20.0.7',
            'port' => '3306',
            'dbname' => 'silanggame_sdk_db001',
            'username' => 'root',
            'password' => 'root',
        ],
	'iad' => [
            'host' => '172.20.0.7',
            'port' => '3306',
            'dbname' => 'silanggame_iad_db001',
            'username' => 'root',
            'password' => 'root',
        ],
    ],
   'postgresql' => [
       'host' => '172.18.0.4',
       'port' => '5432',
       'dbname' => 'data',
       'username' => 'root',
       'password' => 'root',
   ],
    'redis' => [
        'host' => '172.20.0.5',
        'port' => '6379',
        // 'db' => '0',  //选择默认数据库
        'auth' => '',
    ],
    'ssdb' => [
        'host' => '172.18.0.6',
        'port' => '8888',
    ],
];