<?php
/**
 * php mongodb扩展操作类
 * @Author:show
 */
namespace mongox;
use MongoDB\BSON\Regex;
use MongoDB\Driver\BulkWrite;
use MongoDB\Driver\Command;
use MongoDB\Driver\Cursor;
use MongoDB\Driver\Manager;
use MongoDB\Driver\Query;
use MongoDB\Driver\ReadPreference;

use MongoDB\Driver\WriteConcern;
use MongoDB\Driver\Exception\BulkWriteException;
use MongoDB\Driver\Exception\Exception as MongoDBDriverException;

class CreateCollection {
    protected $cmd = array();

    function __construct($collectionName) {
        $this->cmd["create"] = (string)$collectionName;
    }
    function setAutoIndexId($bool) {
        $this->cmd["autoIndexId"] = (bool)$bool;
    }
    function setCappedCollection($maxBytes, $maxDocuments = false) {
        $this->cmd["capped"] = true;
        $this->cmd["size"]   = (int)$maxBytes;

        if ($maxDocuments) {
            $this->cmd["max"] = (int)$maxDocuments;
        }
    }
    function usePowerOf2Sizes($bool) {
        if ($bool) {
            $this->cmd["flags"] = 1;
        } else {
            $this->cmd["flags"] = 0;
        }
    }
    function setFlags($flags) {
        $this->cmd["flags"] = (int)$flags;
    }
    function getCommand() {
        return new Command($this->cmd);
    }
    function getCollectionName() {
        return $this->cmd["create"];
    }
}

/**
 * php mongodb扩展操作类
 * Author:show
 */
class db_mongodb implements mongo_base
{
    //conn相当于manager
    public $conn;
    public $dbname;
    public $collection;
    public function  __construct($host,$username='',$password='',$dbname='')
    {
        self::init_mongo($host,$username,$password,$dbname);
    }
    //初始化mongo
    public function init_mongo($host,$username='',$password='',$dbname='')
    {
        $authString = "";
        if ($username && $password) {
            $authString = "{$username}:{$password}@";
        }
        // $replicaSet && $replicaSet = '?replicaSet='.$replicaSet;
        $dsn = "mongodb://{$authString}{$host}";
        $this->conn = new Manager($dsn);
        if($dbname)
        {
            $this->dbname = $dbname;
        }

    }
    //创建集合
    public function createCollection($collection_name)
    {
        $createCollection = new CreateCollection($collection_name);
        $createCollection->setCappedCollection(1024);
        try {
            $command = $createCollection->getCommand();
            //选择数据库，执行命令
            $cursor = $this->conn->executeCommand($this->dbname, $command);
            $response = $cursor->toArray()[0];

            $collstats = ["collstats" => $createCollection->getCollectionName()];
            $cursor = $this->conn->executeCommand($this->dbname, new Command($collstats));
            $response = $cursor->toArray()[0];
        } catch(MongoDBDriverException $e) {
            echo $e->getMessage(), "\n";
        }
    }
    //选择集合
    public function selectCollection($collection_name)
    {
        $this->collection = $collection_name;
    }
    //选择数据库
    public function select($dbname='db')
    {
        $this->dbname = $dbname;
    }
    //获取信息
    public function get_one($collection_name='', $condition=array(), $fields=array(), $key='')
    {

        $filter = array();
        $query = new Query($filter, $condition);
        $dbcollection = $this->dbname.".".$collection_name;
        $row = $this->conn->executeQuery($dbcollection, $query); 

        $row = $row;

        // $it = new \IteratorIterator($cursor);
        // $it->rewind(); 
        // while($doc = $it->current()) {
        //     $row[] = $doc;
        //     $it->next();
        // }

        return $row;

    }
    //插入
    public function insert($collection_name='', $docarray=array(), $options=array())
    {
        $bulk = new BulkWrite(['ordered' => true]);
        //判断是否有三层
        //为空就没必要插入了
        if(empty($docarray))
        {
            return '';
        }
        $bulk->insert($docarray);
        $writeConcern = new WriteConcern(WriteConcern::MAJORITY, 1000);

        try {
            $dbcollection = $this->dbname.".".$collection_name;
            $result = $this->conn->executeBulkWrite($dbcollection, $bulk, $writeConcern);
        } catch (BulkWriteException $e) {
            $result = $e->getWriteResult();

            /*
            if ($writeConcernError = $result->getWriteConcernError()) {
                printf("%s (%d): %s\n",
                    $writeConcernError->getMessage(),
                    $writeConcernError->getCode(),
                    var_export($writeConcernError->getInfo(), true)
                );
            }

            // Check if any write operations did not complete at all
            foreach ($result->getWriteErrors() as $writeError) {
                printf("Operation#%d: %s (%d)\n",
                    $writeError->getIndex(),
                    $writeError->getMessage(),
                    $writeError->getCode()
                );
            }
            */
        } catch (MongoDBDriverException $e) {
            printf("mongo error!");
            // printf("Other error: %s\n", $e->getMessage());
        }

        // $result->getInsertedCount()
        // $result->getModifiedCount()
        
        
    }
    
}

