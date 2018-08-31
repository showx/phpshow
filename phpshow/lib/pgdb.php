<?php
/**
 * Created by PhpStorm.
 * postgresql连接驱动
 * User: shengsheng
 * Date: 2018/8/31
 * Time: 下午3:04
 */

namespace phpshow\lib;


class pgdb
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
        $config = \phpshow\App::getConfig("db")['postgresql'];
        $host        = "host=".$config['host'];
        $port        = "port=".$config['port'];
        $dbname      = "dbname=".$config['dbname'];
        $credentials = "user={$config['username']} password={$config['password']}";
        $this->conn = pg_connect("$host $port $dbname $credentials");
        if(!$this->conn){
            echo "Error : Unable to open pg";
        }
        return $this->conn;
    }

    /**
     * 安全过滤
     * @param $sql
     * @return mixed
     */
    public function safe_string($sql)
    {
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
//        echo $sql.lr;
        $result = pg_query($this->conn,$sql);

        $endtime = microtime(true);
        $lasttime = $endtime - $starttime;
        if($lasttime>$this->late_time)
        {
            //慢查询，保存到sql日志文件
        }
        if (!$result) {
            $error = 'Invalid query: ' . pg_last_error($this->conn);
            echo $error.lr;
        }
        return $result;
    }

    /**
     * 数据fetch
     * @param $result
     * @param fetch_type [MYSQLI_NUM|MYSQLI_ASSOC|MYSQLI_BOTH]
     */
    public function fetch($result,$fetch_type = PGSQL_ASSOC)
    {
        while($row=pg_fetch_array($result,$fetch_type)) {
            $data[] = $row;
        }
        $this->free( $result );
        return $data;
    }

    /**
     * 检测mysql连接
     */
    public function ping( )
    {
        if( $this->conn != null && !pg_ping( $this->conn ) )
        {
            pg_close( $this->conn );
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
        return pg_free_result( $rs );
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
        $row = pg_fetch_array($result,NULL,PGSQL_ASSOC);
        $this->free($result);
        return $row;
    }

    /**
     * 获取所有数据
     */
    public function get_all($sql)
    {
        $result = $this->query($sql);
        $row = pg_fetch_all($result,PGSQL_ASSOC);
        return $row;
    }

    /**
     * 获取插入的id
     * @return int|string
     */
    public function insert_id()
    {
    }
}