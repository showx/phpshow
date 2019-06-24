<?php
/**
 * 数据库配置文件
 * Author:shengsheng
 */
return [
    //会比普通crondjob多一位配置，精确到秒的意思
    '01 * */2 * * *' => 'a.php', 
    '01 * * * * *' => 'a.php',
    '01 * * * * *' => 'a.php',
];