<?php
/**
 * Created by PhpStorm.
 * User: pengyongsheng
 */
namespace app\model;

use phpshow\model;


class mod_search
{
    public static $pageSize = '';
    public static $dateRange = '';
    /**
     * 转换where
     */
    public static function whereAss($key,$op,$value)
    {
        if($op == '=')
        {
            return " {$key} = {$value} ";
        }elseif($op == 'like')
        {
            return " {$key} like '%{$value}%' ";
        }elseif($op == 'int_in')
        {
            foreach($value as $vkey=>$vval)
            {
                $new_value[] = (int)$vval;
            }
            $new_value = implode("','",$new_value);
            return " {$key} in ('{$new_value}') ";
        }elseif($op == 'in')
        {
            $new_value = implode("','",$value);
            return " {$key} in ('{$new_value}') ";
        }
    }
    /**
     * 获取limit数
     */
    public static function pageLimit($pageSize='',$page='')
    {
        self::$pageSize = $pageSize;
        $current = '0';
        if($page)
        {
            $current = ($page-1) * $pageSize;
        }
        if($current == '0')
        {
            $limit = $pageSize;
        }else{
            $limit = [$current,$pageSize];
        }
        return $limit;
    }
    
    /**
     * 日期搜索
     */
    public static function SearchDate($date,$field = 'recdate')
    {
        $str = '';
        if(!empty($date))
        {
            $date = explode(" / ",$date);
            if($date['0'])
            {
                $starttime = trim($date['0']);
                if($date['1'])
                {
                    $endtime = trim($date['1']);
                    $str = " ( {$field}>='{$starttime}' and {$field}<='{$endtime}') ";
                }else{
                    $str =  " {$field} = '{$starttime}' ";
                }
            }
            
        }
        return $str;
    }
    /**
     * 查看搜索数据
     */
    public static function requestSearch($search_arr)
    {
        $where = [];
        foreach($search_arr as $key=>$op)
        {
            $tmp = \phpshow\request::item($key,"");
            if(!empty($tmp))
            {
                $where[$key] = mod_search::whereAss($key,$op,$tmp);
            }
        }
        $sorter = \phpshow\request::item("sorter","");
        $sorter = self::explainSortArr($sorter);
        if($sorter)
        {
            $where['order'] = $sorter;
        }
        return $where;
    }
    /**
     * 解释排序字段
     * game_id|ascend  字段|升降  asend descend
     */
    public static function explainSortArr($sort_field = '')
    {
        $sort_field = explode("|",$sort_field);
        if(empty($sort_field) || !isset($sort_field['1']))
        {
            return '';
        }
        if($sort_field['1'] == 'ascend')
        {
            $sort_type = 'asc';
        }else{
            $sort_type = 'desc';
        }
        $order[$sort_field['0']] = " {$sort_field['0']} $sort_type ";
        return $order;
    }
}