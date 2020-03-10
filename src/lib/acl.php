<?php
/**
 * 登录之后的处理
 * 权限控制
 * Created by PhpStorm.
 * User: shengsheng
 * Date: 2018/7/19
 * Time: 上午12:44
 */

namespace phpshow\lib;
define("ACL_ALLOW","1");
define("ACL_DEFINE","2");
define("ACL_KNOWN","0");
class AclNode{
    public $leaves;
    public $name;
    public $allow;
    public function __construct($node)
    {
        $this->name = $node;
    }
    public function addChild($nodeName)
    {
        if(isset($this->leaves[$nodeName])){
            $node = $this->leaves[$nodeName];
        }else{
            $node = new AclNode($nodeName);
            $this->leaves[$nodeName] = $node;
        }
        return $node;
    }
    public function setAllow(string $allow): void
    {
        $this->allow = $allow;
    }
    public function isAllow()
    {
        return $this->allow;
    }
    public function search(string $path,AclNode $parentNode = null):?AclNode
    {
        $path = trim($path,'/');
        $list = explode('/',$path);
        $name = array_shift($list);
        if($name == $this->name){
            return $this;
        }
        if(empty($name) && $this->name == '*'){
            return $this;
        }
        if(!empty($name) && !empty($parentNode)){
            return $parentNode;
        }
        if(isset($this->leaves[$name])){
            if(!empty($list)){
                return $this->leaves[$name]->search(implode('/',$list));
            }else{
                return $this->leaves[$name];
            }
        }
        if(isset($this->leaves['*'])){
            if(!empty($list)){
                return $this->leaves['*']->search(implode('/',$list), $this->leaves['*']);
            }else{
                return $this->leaves['*'];
            }
        }
        return null;
    }
}
class Acl
{
    /**
     * 构造
     */
    public function __construct()
    {
        $this->node = new AclNode('*');
    }
    /**
     * 返回权限列表
     */
    public function toList($node = [])
    {
        $list = [];
        foreach($node['leaves'] as $key=>$val)
        {
            $list['leaves'][$key] = $this->toList($val);
        }
        return $list;
    }
    /**
     * 添加规则
     */
    public function add($paths,$policy)
    {
        $paths = explode("/",trim($paths,"/"));
        $node = $this->node;
        foreach($paths as $path)
        {
            $node = $node->addChild($path);
        }
        $node->setAllow($policy);
    }
    /**
     * 检查权限
     */
    public function check($path)
    {
        $node = $this->node->search($path);
        if ($node) {
            return $node->isAllow();
        } else {
            return ACL_KNOWN;
        }
    }

}