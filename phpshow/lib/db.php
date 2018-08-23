<?php
/**
 * Created by PhpStorm.
 * User: pengyongsheng
 * @todo mysql主从
 * Date: 2018/7/19
 * Time: 下午7:29
 */

namespace phpshow\lib;


class db
{
    //数据库链接
    public $link;
    //慢查询时间
    private $late_time = 3;
    public function __construct()
    {
        $this->connect();
    }

    /**
     * 连接数据库
     */
    public function connect()
    {
        $config = \phpshow\App::getConfig("db")['mysql']['master'];
        $this->conn = mysqli_connect($config['host'],$config['username'],$config['password'],$config['dbname'],$config['port']) or die('mysql connect error');
//        mysqli_select_db($this->conn,$config['dbname']);
//        $this->query(" use `{$dbname}`; ");
        return $this->conn;
    }

    /**
     * 安全过滤
     * @param $sql
     * @return mixed
     */
    public function safe_string($sql)
    {
        $sql = mysqli_real_escape_string($this->conn,$sql);
        $safe_array = array("load file","truncate","/*","*/","--");
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
        $starttime = microtime(true);
        $sql = $this->safe_string($sql);
        $result = mysqli_query($this->conn,$sql);
        $endtime = microtime(true);
        $lasttime = $endtime - $starttime;
        if($lasttime>$this->late_time)
        {
            //慢查询，保存到sql日志文件
        }
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
        mysqli_free_result( $result );
        return $data;
    }

    /**
     * 检测mysql连接
     */
    public function ping(  )
    {
        if( $this->conn != null && !mysqli_ping( $this->conn ) )
        {
            mysqli_close( $this->conn );
            @mysqli_close( $this->conn );
            $this->conn = null;
            $this->connect();
        }
    }

    /**
     * 释放集合
     * @param $rs
     */
    public function free( $rs )
    {
        return mysqli_free_result( $rs );
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
        mysqli_free_result($result);
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