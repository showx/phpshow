<?php
/**
 * 数据库pdo
 * Created by PhpStorm.
 * User: pengyongsheng
 * Date: 2018/7/19
 * Time: 下午7:29
 */

namespace phpshow\lib;
USE PDO;
USE PDOException;
USE InvalidArgumentException;


class db
{
    public $dsn;
    public $lastsql;
    public $stmt;
    public function __construct($options)
    {
        
    }


}