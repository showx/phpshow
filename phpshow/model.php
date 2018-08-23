<?php
/**
 * 模型基类
 * Created by PhpStorm.
 * User: shengsheng
 * Date: 2018/7/19
 * Time: 上午12:38
 */

namespace phpshow;


class model
{
    //表格名
    public static $table_name = "";
    //条数
    public $limit;

    static function db(){
        return \phpshow\helper\facade\db;
    }

    /**
     * 数据库名
     * @return string
     */
    static function table(){
        if(static::$table_name === ""){
            $table_name = get_called_class();
            preg_match('#mod_[a-z]+#iu',$table_name,$table);
            $table_name = str_replace("mod_","",$table['0']);
            static::$table_name = $table_name;
        }
        return static::$table_name;
    }

    /**
     * 获取一条数据
     * @return mixed
     */
    public function get_one()
    {
        $table = self::table();
        $sql = "select * from $table order by id";
        $rows = \db::get_one($sql);
        return $rows;
    }

    /**
     * 获取所有数据
     * @return mixed
     */
    public function get_all()
    {
        $table = self::table();
        $sql = "select * from $table order by id";
        $rows = \db::find($sql);
        return $rows;

    }

    /**
     * 分页
     * @param $page
     * @param $size
     * @param string $where
     * @param string $order
     */
    static function paginate($page, $size, $where='', $order=''){

    }

    /**
     * 插入新数据
     * @param $attrs
     */
    static function save($attrs){

    }

    /**
     * 更新数据
     * @param $attrs
     */
    function update($attrs){

    }

    /**
     * 删除数据
     * @param $id
     */
    static function delete($id){

    }

}