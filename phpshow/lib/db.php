<?php
/**
 * Created by PhpStorm.
 * User: pengyongsheng
 * Date: 2018/7/19
 * Time: 下午7:29
 */

namespace phpshow\lib;


class db
{
    public $link;
    public function __construct()
    {
        $config = \phpshow\App::getConfig("db")['mysql'];
        $this->conn = mysqli_connect($config['host'],$config['username'],$config['password'],$config['dbname'],$config['port']) or die('mysql connect error');
//        mysqli_select_db($this->conn,$config['dbname']);
    }

    /**
     * 安全过滤
     * @param $sql
     * @return mixed
     */
    public function safe_string($sql)
    {
        $sql = mysqli_real_escape_string($this->conn,$sql);
        $safe_array = array("load file","truncate");
        str_replace($safe_array,"",$sql);
        return $sql;
    }

    /**
     * 执行mysql处理
     * @param $sql
     * @return bool|\mysqli_result
     */
    public function query($sql)
    {
        $sql = $this->safe_string($sql);
        $result = mysqli_query($this->conn,$sql);
        if (!$result) {
            $mysql_error = 'Invalid query: ' . mysqli_error($this->conn);
        }
        return $result;
    }

    /**
     * 数据fetch
     * @param $result
     * @param fetch_type [MYSQLI_NUM|MYSQLI_ASSOC|MYSQLI_BOTH]
     */
    public function fetch($result,$fetch_type = MYSQLI_ASSOC)
    {
        while($row=mysqli_fetch_array($result,$fetch_type)) {
            $data[] = $row;
        }
        return $data;
    }

    /**
     * 获取单个数据
     */
    public function get_one($sql)
    {
        if(!strpos($sql,'limit'))
        {
            $sql = $sql." limit 1 ";
        }
        $result = $this->query($sql);
        $row = mysqli_fetch_array($result,MYSQLI_ASSOC);
        return $row;
    }

    /**
     * 获取所有数据
     */
    public function get_all($sql)
    {
        $result = $this->query($sql);
        $row = $this->fetch($result);
        return $row;
    }

    /**
     * 获取插入的id
     * @return int|string
     */
    public function mysqli_insert_id()
    {
        return mysqli_insert_id($this->conn);
    }
}