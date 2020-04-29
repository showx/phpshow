<?php
/**
 * Created by PhpStorm.
 * User: pengyongsheng
 * Date: 2018/7/19
 * Time: 下午7:29
 */

namespace phpshow\lib;


class mysql
{
    //数据库链接
    public $link;
    //慢查询时间
    private $late_time = 3;
    public function __construct($conn = 'master')
    {
        $this->connect($conn);
    }

    /**
     * 连接数据库
     */
    public function connect($conn = '')
    {
        if(empty($conn))
        {
            $conn = "master";
        }
        $config = \phpshow\lib\config::get("db.mysql")[$conn];
        $this->conn = mysqli_connect($config['host'],$config['username'],$config['password'],$config['dbname'],$config['port']) or die('mysql connect error');
        if(empty($config['charset']))
        {
            $charset = 'utf-8';
        }else{
            $charset = $config['charset'];
        }
        mysqli_set_charset($this->conn,$charset);
        return $this->conn;
    }

    /**
     * 执行mysql处理
     * @param $sql
     * @return bool|\mysqli_result
     */
    public function query($sql)
    {
        $starttime = microtime(true);
        // echo $sql.lr;
        $result = mysqli_query($this->conn,$sql);
        $endtime = microtime(true);
        $lasttime = $endtime - $starttime;
        if($lasttime>$this->late_time)
        {
            //慢查询，保存到sql日志文件
        }
        // echo "mysql_time:".$lasttime.lr;
        if (!$result) {
            //调试模式才能显示
            if(\phpshow\lib\config::get('site.debug') == 1)
            {
                echo "sql_time:".$lasttime.lr;
                $mysql_error = $sql.'Invalid query: ' . mysqli_error($this->conn);
                echo $mysql_error.lr;
            }
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
        $data = [];
        while($row=mysqli_fetch_array($result,$fetch_type)) {
            $data[] = $row;
        }
        $this->free( $result );
        return $data;
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
        $row = false;
        if(!strpos($sql,'limit'))
        {
            //$sql语句结尾不能有;号
            $sql = $sql." limit 1 ";
        }
        $result = $this->query($sql);
        $row = mysqli_fetch_array($result,MYSQLI_ASSOC);
        $this->free($result);
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
     * 处理大数组
     * @param $sql
     * @return \Generator
     */
    public function get_big_all($sql)
    {
        $result = $this->query($sql);
        while($row=mysqli_fetch_array($result,MYSQLI_ASSOC)) {
            yield $row;
        }
    }

    /**
     * 获取插入的id
     * @return int|string
     */
    public function insert_id()
    {
        return mysqli_insert_id($this->conn);
    }

}