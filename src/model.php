<?php
/**
 * 模型基类
 * 继续Medoo上进行操作
 * Created by PhpStorm.
 * User: shengsheng
 * Date: 2018/7/19
 * Time: 上午12:38
 */

namespace phpshow;

use phpshow\lib\Medoo;

class model extends Medoo
{
    //表格名
    public $table_name = "";
    //每页条数
    public $limit = 25;
    //指定数据库
    public $database = 'master';
    //指定数据库名
    public $db_name = '';
    //当前页数
    public $page = 1;
    //主键
    public $primary_key = 'id';
    //数据库类型，暂时只支持mysql
    public $db_type = 'mysql';
    //查询字段
    public $fields = '*';
    //条件
    public $condition = '';
    //order条件
    public $condition_order = '';
    //sql limit
    public $condition_limit = '';
    //表格数据
    public $attr;

    public function __construct()
    {
        //自动效验表格名
        $this->table();
        $config = \phpshow\lib\config::get("db.mysql")[$this->database];
        $options = [
            'database_type' => $this->db_type,  //'mysql',
            'database_name' => !empty($this->db_name)?$this->db_name:$config['dbname'],
            'server' => $config['host'],
            'username' => $config['username'],
            'password' => $config['password'],
            'charset' => 'utf8',
            'port' => 3306,
        ];
        parent::__construct($options);
    }

    public function create()
    {
        return new static();
    }

    public function __set($key,$value)
    {
        $this->attr[$key] = $value;
    }
    
    public function __get($key)
    {
        return $this->attr[$key];
    }

    /**
     * 数据库名
     * @return string
     */
    public function table($table_name = ''){
        if(!empty($table_name))
        {
            $this->table_name = $table_name;
        }
        if($this->table_name === ""){
            $table_name = get_called_class();
            $table_name = str_replace(["mod_","Model"],"",$table_name);
            $this->table_name = $table_name;
        }
        return $this;
    }

    /**
     * get_one
     */
    public function get_one($where = [])
    {
        return parent::get($this->table_name,$this->fields,$where);
    }

    /**
     * 返回所有数据
     */
    public function get_all($where = [])
    {
        $limit = [($this->page-1) * $this->limit,$this->limit];
        $where['LIMIT'] = $limit;
        return parent::select($this->table_name,$this->fields,$where);
    }

    /**
     * 列出列表
     */
    public function list($where = [])
    {
        $data = $this->get_all($where);
        $total = $this->count($this->table_name,$where);
        return [
            'list' => $data,
            'total' => $total
        ];
    }

    /**
     * 插入新数据
     * @param $attrs
     */
    public function insert1($attrs = '')
    {

        if(empty($attrs) && !empty($this->attr) )
        {
            $attrs = $this->attr;
        }
        parent::insert($this->table_name,$attrs);
        
        if($this->error()['0'] != '00000')
        {
            // var_dump($this->error());
            //debug下打印一下
            return false;
        }else{
            $insert_id = $this->id();
        }

        return $insert_id;
    }

    /**
     * 更新数据
     * @param $attrs
     */
    public function update1($attrs,$where){
        //这个里where
        $data = parent::update($this->table_name,$attrs,$where);
        return  $data->rowCount();
    }

    /**
     * 删除数据
     * 只针对id处理
     * @param $id
     */
    public function delete1($id){
        parent::delete($this->table_name,['id'=>$id]);
    }

}