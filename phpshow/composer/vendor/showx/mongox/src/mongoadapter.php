<?php
namespace mongox;
/**
 * mongodb接口
 * mongo和mongodb扩展区别大，但使用方法都是一样的
 * 语法类似bone,第一版先实现日志主要的方法
 * @author show
 * @version 1.0
 */
interface mongo_base
{
    //初始化mongo
    function init_mongo($host,$username,$password,$dbname);
    //创建集合
    function createCollection($collection_name);
    //选择集合
    function selectCollection($collection_name);
    //选择或切换数据库
    function select($dbname);
    //获取信息
    function get_one( $collection_name='', $condition=array(), $fields=array(), $key='' );
    //插入
    function insert( $collection_name='', $docarray=array(), $options=array() );
}
/**
 * mongo适配器
 */
class mongoadapter
{
    public $drive;
    public $conf;
    public function __construct($hostserver='127.0.0.1',$port='27017',$user='',$pass='',$dbname='')
    {
        $host = $hostserver.":".$port;
        //选择合适的驱动
        if(extension_loaded("Mongo"))
        {
            $this->drive = new db_mongo($host,$user,$pass,$dbname);
        }else{
            //class_exists("MongoDB\Driver\Manager")
            if(!extension_loaded("Mongodb"))
            {
                echo 'no mongodb drive';
                return '';
            }
            $this->drive = new db_mongodb($host,$user,$pass,$dbname);
        }    
    }
    /**
     * 调用mongo对象
     * @param func 方法
     * @param parameter 参数
     */
    public function __call($func, $parameter) {
        if ($this->drive) return call_user_func_array(array(&$this->drive,$func),$parameter);
        return true;
    }
}
