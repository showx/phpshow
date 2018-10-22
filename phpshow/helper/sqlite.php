<?php
namespace phpshow\helper;
/**
 * sqlite封装类
 * Author:show
 **/
class sqlite
{
    private $handle;
    
    /**
     * 打开数据库
     * @param $location
     * @param $mode
     * @return SQLite3
     */
    public function open($location,$mode)
    {
        $this->handle = new SQLite3($location);
    }
    
    /**
     * 查询数据
     * @param $query
     * @return mixed
     */
    public function query($query)
    {
        $result = $this->handle->query($query);
        return $result;
    }
    
    /**
     * 执行语句
     * @param $query
     * @return mixed
     */
    public function exec($query)
    {
        $result = $this->handle->exec($query);
        return $result;
    }
    
    /**
     * 获取数据
     * @param $result
     * @param $type[1获取列名|0获取数据]
     * @return mixed
     */
    public function fetch_array(&$result,$type='0')
    {
        if($type==1)
        {
            $i = 0;
            //循环出列名
            while ($result->columnName($i))
            {
                $columns[ ] = $result->columnName($i);
                $i++;
            }
            return $columns;
        }
        $resx = $result->fetchArray(SQLITE3_ASSOC);
        return $resx;
    }
    
    /**
     * 兼容本来的框架
     * @param $query
     */
    public function get_one($query)
    {
        $ret = $this->query($query);
        $result = $this->fetch_array($ret);
        return $result;
    }
    
    /**
     * 获取所有数据
     * @param $query
     * @return bool
     */
    public function get_all($query)
    {
        $ret = $this->query($query);
        while ($row = $ret->fetchArray(SQLITE3_ASSOC)) {
            $result[] = $row;
        }
        return $result;
    }
}
