<?php
/**
 * control基类
 * Created by PhpStorm.
 * User: shengsheng
 * Date: 2018/7/19
 * Time: 上午12:37
 */

namespace phpshow;
use \phpshow\lib\tpl;

class control
{
    //是否ajax请求
    public $is_ajax = false;
    //是否校验token
    public $auth_token_page = 0;
    public $auth_data;
    //默认使用的页数
    public $pageSize = '20';
    public $commander;
    //默认时间范围为7天
    public $date_type = "7";
    //[1本天|2昨天|7一周|8月]
    public $date_type_range = ['1'=>'0','2'=>'1','7'=>'7','8'=>'30'];
    public function __construct()
    {

        if(\phpshow\request::$request_mdthod == 'OPTIONS')
        {
            \phpshow\response::send("1");
            return false;
        }


        if($this->auth_token_page == 1)
        {
            $this->authtoken();
        }

        $this->is_ajax = $this->is_ajax();
        if(!self::_Acl())
        {
            die('no access');
        }
    }

    /**
     * auth token验证
     */
    public function authtoken($authorization = '')
    {
        $this->auth_data = [];
        //HTTP_X_TOKEN
        if(isset($_SERVER['HTTP_AUTHORIZATION']))
        {
            $authorization = $_SERVER['HTTP_AUTHORIZATION'];
        }else{
            if(isset(\phpshow\request::$header['authorization']))
            {
                $authorization = \phpshow\request::$header['authorization'];
            }
        }
        if(!empty($authorization))
        {
            $jwt = new \phpshow\lib\jwt();
            //验证authorization
            $data = $jwt->decode($authorization);
            
        }else{
            $data = false;
        }
        if($data == false)
        {
            if(\phpshow\request::$request_mdthod == 'OPTIONS')
            {
                // echo 'options'.lr;
                \phpshow\response::toJson(['cors'=>'options']);
                return false;
            }else{
                \phpshow\response::code("unauth");
                \phpshow\response::toJson(['code'=>'-1','msg'=>'unauth']);
                return false;
            }
        }
        //可实时验证一下status
        $this->auth_data = $data;
        return $data;
    }
    
    /**
     * 默认选择时间的范围
     */
    public function dateRange($day=7)
    {
        if(empty($this->date_type))
        {
            return '';
        }
        //7天之内
        if($this->date_type=='7')
        {
            $day = '7';
        }
        $day = $this->date_type_range[$this->date_type];
        $endtime = date("Ymd",time());
        $starttime = date("Ymd",time()-(86400*$day));
        return "{$starttime} / {$endtime}";
    }
    /**
     * 判断权限
     */
    public function _Acl()
    {
        return true;
    }

    /**
     * 判断是否ajax请求
     * @return bool
     */
    public function is_ajax()
    {
        defined("PS_ISAJAX") or define("PS_ISAJAX","0");
        if(PS_ISAJAX) return true;
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtoupper($_SERVER['HTTP_X_REQUESTED_WITH'])=='XMLHTTPREQUEST';
    }

    /**
     * 输出版本
     */
    final public function welcome()
    {
        echo 'phpshow';
    }
}