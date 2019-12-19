<?php
namespace app\model;
/**
 * 计算模型
 * @todo 类似这种该定义为服务service
 * Author:show
 */
Class mod_calculate
{
    /**
     * 判断是否浮点型
     * 函数is_float判断没那么灵活
     */
    public static function isFloat($value)
    {
        return ((int)$value != $value);
    }
    /**
     * 获取在数组第几位
     * @param $result
     * @return mixed
     */
    public static function forRankValue($result)
    {
        $i=1;
        foreach($result as $key=>$val)
        {
            $result[$key] = $i;
            $i++;
        }
        return $result;
    }
    /**
     * 对数值的格式化
     * @param num 数值
     * @param retain 保留的小数位
     * @param way 方式 [1普通保留|2大于保留位才保留]
     */
    public static function nformat($num, $retain = '2', $way = '1')
    {
        if (empty($num)) {
            return $num;
        }
        $is_float = self::isFloat($num);
        if (is_nan($num)) {
            $num = 0;
        }
        if ($is_float && $num) {
            if ($way == '2') {
                $nums = end(explode('.',$num));
                if (strlen($nums) >= $retain) {
                    $num = number_format($num,$retain, ".", "");
                }
                return $num;
            }
            //小数点保留两位小数，并四舍五入
            $num = number_format($num,$retain, ".", "");
        }
        return $num;
    }
    
    /**
     * 占比上期
     * 如果上期收入为80000，本期收入为2000
     * 那么占比上期收入为2000/80000*100%=2.5%
     */
    public static function getProportion($cur_data,$past_data,$percentage=false)
    {
        $prop = $cur_data/$past_data;
        $prop = self::nformat($prop);
        if($percentage)
        {
            $prop = ($prop * 100).'%';
        }
        return $prop;
    }
}