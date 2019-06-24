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
    public $table_name = "";
    //条数
    public $limit = 25;
    //页数
    public $page = 1;
    public $primary_key = 'id';
    public $db_type = 'mysql';
    public $db = '';
    public $fields = '*';
    public $condition = '';
    public $condition_limit = '';
    //完成rest资源get post put delete
    public function __construct()
    {
        $this->table();
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
            preg_match('#mod_[a-z]+#iu',$table_name,$table);
            $table_name = str_replace("mod_","",$table['0']);
            $this->table_name = $table_name;
        }
        return $this->table_name;
    }
    /**
     *  切换数据驱动
     */
    public function dbinstance()
    {
        if(empty($this->db))
        {
            if($this->db_type == 'mysql')
            {
                $this->db = new db();
            }else{
                $this->db = new pgdb();
            }
        }
        return $this->db;
    }
    
    /**
     * 获取一条数据
     * @return mixed
     */
    public function get_one($sql)
    {
        if(empty($sql))
        {
            $sql = "select * from {$this->table_name} ";
        }
        $rows = $this->dbinstance()->get_one($sql);
        return $rows;
    }

    /**
     * 获取所有数据
     * @return mixed
     */
    public function get_all($sql = '')
    {
        if(empty($sql))
        {
            $sql = "select * from {$this->table_name} ";
        }
        $rows = $this->dbinstance()->get_all($sql);
        return $rows;

    }
    public function find()
    {
        $sql = " select {$this->fields} from {$this->table_name} {$this->condition}";
        return $this->get_one($sql);
    }
    public function findAll()
    {
        $sql = " select {$this->fields} from {$this->table_name} {$this->condition} {$this->condition_limit}";
        return $this->get_all();
    }
    /**
     * 字段
     */
    public function column($fields)
    {
        if(is_array($fields))
        {
            $this->fields = implode($fields,",");
        }else{
            $this->fields = $fields;
        }
        return $this;
    }
    /**
     * column别名
     */
    public function select($fields)
    {
        return $this->column($fields);
    }
    
    /**
     * 条件
     */
    public function where($where)
    {
        if(empty($where))
        {
            return '';
        }
        if(is_array($where))
        {
			$conditions = array_diff_key($where, array_flip(
				['group', 'order', 'having', 'limit', 'like']
            ));
            $where_clause = '';
            if (!empty($conditions))
			{
                foreach($conditions as $key=> $value)
                {
                    $condition_arr[] = " {$where[$key]}  ";
                }
				$where_clause = ' WHERE ' . implode($condition_arr, ' and ');
            }
            if(isset($where['group']))
            {
                if(is_array($where['group']))
                {
                    $where_clause .= ' GROUP BY ' . implode($where['group'], ',');
                }else{
                    $where_clause .= ' group by ' . $where['group'];
                }
            }
            if(isset($where['having']))
            {
                if(is_array($where['having']))
                {
                    $where_clause .= ' having  ' . implode($where['having'], ',');
                }else{
                    $where_clause .= ' having  ' . $where['having'];
                }
            }
            if(isset($where['order']))
            {
                if(is_array($where['order']))
                {
                    $where_clause .= ' order by ' . implode($where['order'], ',');
                }else{
                    $where_clause .= ' order by ' . $where['order'];
                }
            }
            if(isset($where['limit']))
            {
                if(is_array($where['limit']))
                {
                    $this->condition_limit = ' LIMIT ' . $where['limit'][1] . ' OFFSET ' . $where['limit'][0];
                }else{
                    $this->condition_limit .= ' LIMIT ' . $where['limit'];
                }
            }
        }else{
            $where_clause = $where;
        }
        $this->condition = $where_clause;
        return $this;
    }


    /**
     * 分页
     * @param $page
     * @param $size
     * @param string $where
     * @param string $order
     */
    public function pageData($where_tmp='')
    {
        $where = '';
        if($where_tmp)
        {
            $where = " where {$where_tmp}";
        }
        if($this->condition && empty($where2))
        {
            $where = $this->condition;
        }
        $data_sql = "select {$this->fields} from {$this->table_name} {$where} {$this->condition_limit}";
        // echo $data_sql;exit();
        $data = $this->dbinstance()->get_all($data_sql);
        $one = $this->select(" count(1) as c ")->find();
        $rows['list'] = $data;
        $rows['total'] = (int)$one['c'];
        
        return $rows;
    }

    /**
     * 插入新数据
     * @param $attrs
     */
    public function insert($attrs)
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
        $sql = "insert into {$this->table_name}(`{$keyss}`) values('{$valuess}') ";
        $this->dbinstance()->query($sql);
        return $this->dbinstance()->insert_id();
    }

    /**
     * 更新数据
     * @param $attrs
     */
    public function update($attrs,$where){
        if(!is_array($attrs) && empty($where))
        {
            return false;
        }
        $update_arr = [];
        foreach($attrs as $key=>$val)
        {
            $update_arr[] = " `{$key}`='{$val}' ";
        }
        $update_string = implode(',',$update_arr);
        $sql = "update {$ths->table_name} set {$update_string} where {$where} ";
        // echo $sql.lr;
        self::dbinstance()->query($sql);
    }

    /**
     * 删除数据
     * 危险操作
     * @param $id
     */
    public function delete($id){
        $sql = "delete  from {$this->table_name} where `{$this->primary_key}`='{$id}' ";
        $rows = self::dbinstance()->query($sql);
        return $rows;
    }

}