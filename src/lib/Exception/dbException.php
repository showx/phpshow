<?php
/**
 * db异常类
 * Author:show
 */
namespace phpshow\lib\Exception;
Class dbException extends \Exception
{
    private $errorCode;
    private $sql;

    /**
     * 初始化异常
     */
    public function __construct($code = "", $message = "",  $sql = "")
    {
        parent::__construct($message, 0);
        $this->errorCode = $code;
        $this->sql = $sql;
    }

    /**
     *  获取错误码
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }

    /**
     * 获取错误的sql语句
     */
    public function getSql():string
    {
        return $this->sql;
    }

    /**
     * 格式化输出异常码，异常信息，请求id
     * @return string
     */
    public function __toString()
    {
        return "[".__CLASS__."]"." code:".$this->errorCode.
            " message:".$this->getMessage().
            " sql:".$this->sql.lr;
    }

}