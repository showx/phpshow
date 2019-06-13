<?php
/**
 * 模型基类
 * Created by PhpStorm.
 * User: shengsheng
 * Date: 2018/7/19
 * Time: 上午12:38
 */

namespace phpshow;

use phpshow\lib\db;
use phpshow\lib\pgdb;
class model
{
    //表格名
    public static $table_name = "";
    //条数
    public static $limit = 25;
    //页数
    public static $page = 1;
    public static $primary_key = 'id';
    public static $db_type = 'mysql';
    public static $db = '';
    //完成rest资源get post put delete
    /**
     * 数据库名
     * @return string
     */
    public static function table(){
        if(static::$table_name === ""){
            $table_name = get_called_class();
            preg_match('#mod_[a-z]+#iu',$table_name,$table);
            $table_name = str_replace("mod_","",$table['0']);
            static::$table_name = $table_name;
        }
        return static::$table_name;
    }

    public static function dbinstance()
    {
        if(empty(static::$db))
        {
            if(static::$db_type == 'mysql')
            {
                self::$db = new db();
            }else{
                self::$db = new pgdb();
            }
        }
        return self::$db;
    }
    
    /**
     * 获取一条数据
     * @return mixed
     */
    public static function get_one($sql)
    {
        $table = self::table();
        if(empty($sql))
        {
            $sql = "select * from {$table} ";
        }
        $rows = self::dbinstance()->get_one($sql);
        return $rows;
    }

    /**
     * 获取所有数据
     * @return mixed
     */
    public static function get_all($sql = '')
    {
        $table = self::table();
        if(empty($sql))
        {
            $sql = "select * from {$table} ";
        }
        $rows = static::dbinstance()->get_all($sql);
        return $rows;

    }

    /**
     * 分页
     * @param $page
     * @param $size
     * @param string $where
     * @param string $order
     */
    public static function page_data($page, $where='',$fields = '*',$group = '',$order='')
    {
        $table = self::table();
        $limit = self::$limit;
        $offset = $limit * ($page - 1);
        // $data_sql = "select * from {$table}  where 1 {$where} limit {$offset},{$limit} ";
        if($where)
        {
            $where = " where {$where}";
        }
        $data_sql = "select {$fields} from {$table} {$where} {$group} {$order} limit {$limit} OFFSET {$offset} ";
        $data = static::dbinstance()->get_all($data_sql);
        $count_sql = "select count(1) as c from {$table} {$where}";
        $one = self::dbinstance()->get_one($count_sql);
        $rows['list'] = $data;
        $rows['pagination'] = [
            'total' => (int)$one['c'],
            'pageSize' => (int)$limit,
            'current' => (int)$page,
        ];
        return $rows;
    }

    /**
     * 插入新数据
     * @param $attrs
     */
    public static function save($attrs)
    {
        if(!is_array($attrs))
        {
            return false;
        }
        $table = self::table();
        $arr_key = array_keys($attrs);
        $arr_value = array_values($attrs);
        $keyss = implode('`,`',$arr_key);
        $valuess = implode("','",$arr_value);
        $sql = "insert into {$table}(`{$keyss}`) values('{$valuess}') ";
        self::dbinstance()->query($sql);
        return self::dbinstance()->insert_id();
    }

    /**
     * 更新数据
     * @param $attrs
     */
    public static function update($attrs,$where){
        if(!is_array($attrs) && empty($where))
        {
            return false;
        }
        $table = self::table();
        $update_arr = [];
        foreach($attrs as $key=>$val)
        {
            $update_arr[] = " `{$key}`='{$val}' ";
        }
        $update_string = implode(',',$update_arr);
        $sql = "update {$table} set {$update_string} where {$where} ";
        // echo $sql.lr;
        self::dbinstance()->query($sql);
    }

    /**
     * 删除数据
     * @param $id
     */
    public static function delete($id){
        $table = self::table();
        $field = self::$primary_key;
        $sql = "delete  from {$table} where `{$field}`='{$id}' ";
        $rows = self::dbinstance()->query($sql);
        return $rows;
    }

}