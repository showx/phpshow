<?php
/**
 * Created by PhpStorm.
 * User: pengyongsheng
 * @todo mysql主从
 * @todo 增加列存储infobright
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
//        mysqli_select_db($this->conn,$config['dbname']);
//        $this->query(" use `{$dbname}`; ");
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
     * 安全过滤
     * @param $sql
     * @return mixed
     */
    public function safe_string($sql)
    {
        //values addslashes的时候处理
//        $sql = mysqli_real_escape_string($this->conn,$sql);
//        $safe_array = array("load file","truncate","--");
//        str_replace($safe_array,"",$sql);
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
        $result = mysqli_query($this->conn,$sql);

        $endtime = microtime(true);
        $lasttime = $endtime - $starttime;
        if($lasttime>$this->late_time)
        {
            //慢查询，保存到sql日志文件
        }
        if (!$result) {
            //调试模式才能显示
            if(\phpshow\Loader::$master->config['site']['debug'] == 0)
            {
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
     * 检测mysql连接
     */
    public function ping(  )
    {
        if( $this->conn != null && !mysqli_ping( $this->conn ) )
        {
            mysqli_close( $this->conn );
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

    /**
     * 预处理方式处理
     * @param $query
     * @param array $args
     * @return array|bool|\mysqli_result
     */
    public function prepare_query($query, array $args = [])
    {
        $result = false;
        $stmt = mysqli_stmt_init($this->conn);
        if(mysqli_stmt_prepare($stmt,$query))
        {
            $params = [];
            $types  = array_reduce($args, function ($string, &$arg) use (&$params) {
                $params[] = &$arg;
                if (is_float($arg))         $string .= 'd';
                elseif (is_integer($arg))   $string .= 'i';
                elseif (is_string($arg))    $string .= 's';
                else                        $string .= 'b';
                return $string;
            }, '');
            if($params && $types)
            {
                mysqli_stmt_bind_param($stmt,$types,...$params);
            }
            $execute = mysqli_stmt_execute($stmt);
            if($execute)
            {
                $result = mysqli_stmt_get_result($stmt);
            }
            mysqli_stmt_close($stmt);
        }
        return $result;
    }

}