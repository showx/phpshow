<?php
namespace phpshow\helper;

//可compose导入
require_once PS_HELPER_PATH . '/phpexcel/PHPExcel.php';
// $cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp;
// $cacheSettings = array( 'memoryCacheSize' => '8MB');
// PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
/**
 * excel操作类
 * 不超过26个列
 * @Author:show
 **/
class excel
{
    //PHPEcel对象
    public $exc;
    //ActiveSheet缩写
    public $as;
    //默认自输出序号 导出的表格是否需要序号这一列
    public $serial_number = 0;
    //导出的数据列
    public $fields;
    //列范围
    public $col_range;
    //需要转换成字符串的字段  使用计费id要导出字符类
    public $stringfields = array("cid");
    //excel数组
    public $data_arr = array();
    //编码
    private $coding;
    //文档头
    private $header = "<?xml version=\"1.0\" encoding=\"%s\"?\>\n<Workbook xmlns=\"urn:schemas-microsoft-com:office:spreadsheet\" xmlns:x=\"urn:schemas-microsoft-com:office:excel\" xmlns:ss=\"urn:schemas-microsoft-com:office:spreadsheet\" xmlns:html=\"http://www.w3.org/TR/REC-html40\">";
    //表标题
    private $tWorksheetTitle;
    //文件名
    private $filename;
    //导出的类型
    private $type;
    //打印是否横向
    public $print_lanscape = false;
    
    /**
     * cls_excel construct
     * @param string $type 类型[1excel导出|2xml导出|3csv格式导出]
     */
    public function __construct($type='1')
    {
        $this->type = $type;
        $this->col_range = $this->createColumnsArray('BZ');
        if($type==1)
        {
            $this->exc = new PHPExcel();
            $this->exc->setActiveSheetIndex(0);
            $this->as = $this->exc->getActiveSheet();
            $rowHeight = 20;
            $this->as->getDefaultRowDimension()->setRowHeight($rowHeight);
        }
    }
    
    /**
     * 设置字段
     * @param key 键名
     * @param val 值 int|string
     * @param type 转换字符串或数值 [strs字符串|valu数值]
     */
    public function setCell($key, $val, $type = "values")
    {
        if(!empty($this->as))
        {
            $this->as->setCellValue($key, $val);
            //转换为字符串类型
            if ($type == 'strs') {
                $this->as->setCellValueExplicit($key, $val, PHPExcel_Cell_DataType::TYPE_STRING);
                $this->as->getStyle($key)->getNumberFormat()->setFormatCode("@");
            }
        }else{
            $col = $row = "";
            //这里可改为正则提取
            $keytmp = str_split($key);
            foreach($keytmp as $tmp)
            {
                if(is_numeric($tmp))
                {
                    $row .= $tmp;  //行
                }else{
                    $col .= $tmp;  //列
                }
            }
//            $val = "0098";  //test csv模式下在excel会变成0098
            $this->data_arr[$row][$col] = $val;
        }
    }
    
    /**
     * 设置水平居中
     * @param $field 需要水平居中的字段
     * @param $h  水平对齐方式
     * @param $v  垂直对齐方式
     * const HORIZONTAL_GENERAL				= 'general';
    const HORIZONTAL_LEFT					= 'left';
    const HORIZONTAL_RIGHT					= 'right';
    const HORIZONTAL_CENTER					= 'center';
    const HORIZONTAL_CENTER_CONTINUOUS		= 'centerContinuous';
    const HORIZONTAL_JUSTIFY				= 'justify';
    const HORIZONTAL_FILL				    = 'fill';
    const HORIZONTAL_DISTRIBUTED		    = 'distributed';        // Excel2007 only

    * Vertical alignment styles
    const VERTICAL_BOTTOM					= 'bottom';
    const VERTICAL_TOP						= 'top';
    const VERTICAL_CENTER					= 'center';
    const VERTICAL_JUSTIFY					= 'justify';
    const VERTICAL_DISTRIBUTED		        = 'distributed';        // Excel2007 only
     */
    public function setCenter($field,$h="center",$v="center")
    {
        $this->as->getStyle($field)->getAlignment()->setHorizontal($h);
        $this->as->getStyle($field)->getAlignment()->setVertical($v);
    }
    
    /**
     * 是否自动换行
     * @param bool $set
     */
    public function setWrap($field,$set=true)
    {
        $this->as->getStyle($field)->getAlignment()->setWrapText($set);
    }
    
    /**
     * 设置列的宽度
     * @param $column
     * @param $width
     */
    public function setColumnWidth($column,$width)
    {
        $this->as->getColumnDimension($column)->setWidth($width);
    }
    
    /**
     * 设置行的高度
     * @param $row
     * @param $height
     */
    public function setRowHeight($row,$height)
    {
        $this->as->getRowDimension($row)->setRowHeight($height);
    }
    
    /**
     * 合并指定区间的单元格
     * @param $section_1
     * @param $section_2
     */
    public function setMerge($section_1,$section_2)
    {
        $merge_string = "{$section_1}:{$section_2}";
        $this->as->mergeCells( $merge_string);
    }
    
    /**
     * 设置字体颜色
     * @param $field 要设置的字段
     * @param color 字体颜色
     * 内置常用字体颜色
     * const COLOR_BLACK						= 'FF000000';
     * const COLOR_WHITE						= 'FFFFFFFF';
     * const COLOR_RED							= 'FFFF0000';
     * const COLOR_DARKRED						= 'FF800000';
     * const COLOR_BLUE						= 'FF0000FF';
     * const COLOR_DARKBLUE					= 'FF000080';
     * const COLOR_GREEN						= 'FF00FF00';
     * const COLOR_DARKGREEN					= 'FF008000';
     * const COLOR_YELLOW						= 'FFFFFF00';
     * const COLOR_DARKYELLOW					= 'FF808000';
     */
    public function setColor($field,$color='')
    {
        if(empty($color))
        {
            $color = PHPExcel_Style_Color::COLOR_RED;
        }
        $this->as->getStyle( $field)->getFont()->getColor()->setARGB($color);
    }
    
    /**
     * 设置字体属性(常用的加上，斜体之类不使用)
     * @param $field 需要设置的字段
     * @param bool $bold 是否需要加粗
     * @param number $size 字体大小
     */
    public function setFont($field,$bold=true,$size='')
    {
        $this->as->getStyle($field)->getFont()->setBold($bold);
        if($size)
        {
            $this->as->getStyle($field)->getFont()->setSize($size);
        }
    }
    
    /**
     * 设置富文本
     * @param $text
     * @param $way [1面对金钱数据]
     */
    public function setRichText($text=array(),$way=1)
    {
        $objRichText = "";
        if ($text) {
            if ($way == 1) {
                $objRichText = new PHPExcel_RichText();
                $objRichText->createText('');
                $objRichText->createTextRun('￥')->getFont()->setColor(new PHPExcel_Style_Color(PHPExcel_Style_Color::COLOR_RED));
                foreach ($text as $key => $val) {
                    if ($key % 2) {
                        $objRichText->createTextRun($val);
                    } else {
                        $objRichText->createTextRun($val)->getFont()->setColor(new PHPExcel_Style_Color(PHPExcel_Style_Color::COLOR_RED));
                    }
                }
            
            }
        }
        if ($objRichText)
        {
            $this->as->getCell('A18')->setValue($objRichText);
        }
    }
    
    /**
     * 批量更新设置
     */
    public function batchConfig($configArr)
    {
        
    }
    
    
    /**
     * 键值范围
     * @param end_column 结束的键
     */
    public function createColumnsArray($end_column, $first_letters = '')
    {
        $columns = array();
        $length = strlen($end_column);
        $letters = range('A', 'Z');
        
        foreach ($letters as $letter) {
            $column = $first_letters . $letter;
            $columns[] = $column;
            if ($column == $end_column)
                return $columns;
        }
        foreach ($columns as $column) {
            if (!in_array($end_column, $columns) && strlen($column) < $length) {
                $new_columns = $this->createColumnsArray($end_column, $column);
                $columns = array_merge($columns, $new_columns);
            }
        }
        return $columns;
    }
    
    /**
     * 设置哪个字段是字符串导出
     * @param strarr array []
     */
    public function setStringFields($strarr)
    {
        $this->stringfields = $strarr;
    }
    
    /**
     * 设置标头,循环出每列标题
     * @param fields 需要输出的字段
     * @param row 行数 一个工作表最多65536行
     */
    public function setfields($fields,$row='2000')
    {
        if ($fields) {
            $this->fields = $fields;
            $i = 0;
            foreach ($fields as $key => $col_title) {
                if ($this->serial_number == 1 && $i == 0) {
                    $this->setCell("A1", "序号");
                    $i++;
                }
                $this->setCell($this->col_range[$i] . "1", $col_title);
                $i++;
            }
        }
    }
    
    /**
     * @desc 存放数据 按列设置数据
     * @param $data array() 需要循环的数据,对应setfields的字段
     */
    public function setData($data)
    {
        if ($data) {
            //避免key不是从0按顺序排列
            $data = array_values($data);
            foreach ($data as $key => $val) {
                if ($val) {
                    //序号
                    $num_key = $key + 1;
                    //第一行是标题A1 数据就要从A2开始
                    $serial_key = $key + 2;
                    if (is_array($val))
                    {
                        $i = 0;
                        //根据fields来循环出来
                        foreach ($this->fields as $k => $v) {
                            if ($this->serial_number == 1 && $i == 0) {
                                //设置序号number,第几行
                                $this->setCell("A" . $serial_key, $num_key);
                                $i++;
                            }
                            $type = "values";
                            if (!empty($this->stringfields) && in_array($k, $this->stringfields)) {
                                $type = "strs";
                            }
                            $this->setCell($this->col_range[$i] . $serial_key, $val[$k], $type);
                            $i++;
                        }
                        
                    }
                }
            }
        }
    }

    /**
     * 输出
     * @param title 标题
     * @param fubiao 没副标题，就使用正标题，副标题主要自动加上连接时间
     */
    public function save($title = 'excel', $subhead = '')
    {
        $sTitle = mb_substr($title,0,"10")."..";
        if ($subhead) {
            $title = $subhead . date('YmdHis') . '.xls';
        }
        if($this->type==1)
        {
            $suffix = stristr($title, ".xls");
            if ($suffix == false) {
                $title .= ".xls";
            }
            $this->as->setTitle($sTitle);
            //打印横向
            if($this->print_lanscape)
            {
                //横向
                $this->as->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                //A4纸
                $this->as->getPageSetup()->setPaperSize(\PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
                $this->as->getPageSetup()->setFitToWidth('1');
                //====================以下针对报销单的设置============
                //改成config设置间距
                $this->as->getPageSetup()->setScale(76);
                //(1英寸 = 2.54厘米)  phpexcel 中是按英寸来计算的,所以这里换算了一下
                $this->as->getPageMargins()->setTop(1.17 / 2.54);
                $this->as->getPageMargins()->setBottom(1.09 / 2.54);
                $this->as->getPageMargins()->setLeft(0 / 2.54);  //0.84
                $this->as->getPageMargins()->setRight(0 / 2.54);  //0.99
                $this->as->getPageMargins()->setHeader(0.8 / 2.54);
                $this->as->getPageMargins()->setFooter(0.8 / 2.54);
            }
            //默认输出 excel5
            //echo "Peak memory usage: " . (memory_get_peak_usage(true) / 1024 / 1024) . " MB";exit;
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename=' . $title);
            header('Cache-Control: max-age=0');
            $objWriter = PHPExcel_IOFactory::createWriter($this->exc, 'Excel5');
//            $objWriter->setPreCalculateFormulas(true);
            $objWriter->save('php://output');
            exit;
        }elseif($this->type=='2') {
            $suffix = stristr($title, ".xls");
            if ($suffix == false) {
                $title .= ".xls";
            }
            $this->xml_display($title,$this->data_arr);
            exit();
        }elseif($this->type=='3')
        {
            $suffix = stristr($title, ".csv");
            if ($suffix == false) {
                $title .= ".csv";
            }
            $data = $this->data_arr;
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename='.$title);
            header('Cache-Control: max-age=0');
            $fp = fopen('php://output', 'a');
            $num = 0;
            //每隔$limit行，刷新一下输出buffer，不要太大，也不要太小
            $limit = 100000;
            //逐行取出数据，不浪费内存
            $count = count($data);
            if ($count > 0) {
                for ($i = 0; $i < $count; $i++) {
                    $num++;
                    //刷新一下输出buffer，防止由于数据过多造成问题
                    if ($limit == $num) {
                        ob_flush();
                        flush();
                        $num = 0;
                    }
                    $row = $data[$i];
                    foreach ($row as $key => $value) {
                        $row[$key] = iconv('utf-8', 'gbk', $value);
                    }
//                    $row['A'] = '0098';
                    fputcsv($fp, $row,',');
                }
            }
            fclose($fp);
            exit();
        }
        
    }
    
    /**
     * 测试方法等，需改为phpunit
     */
    public function test()
    {
        var_dump($this->exc);
    }
    
    //==============================================以下是xml输出类===========================================
    /**
     * Excel基础配置
     * @param string $coding 编码
     * @param boolean $boolean 转换类型
     * @param string $title 表标题
     * @param string $filename Excel文件名
     * @return void
     */
    private function config($enCoding, $boolean, $title, $filename)
    {
        //编码
        $this->coding = $enCoding;
        //转换类型
        if ($boolean == true) {
            $this->type = 'Number';
        } else {
            $this->type = 'String';
        }
        //表标题
        $title = preg_replace('/[\\\|:|\/|\?|\*|\[|\]]/', '', $title);
        $title = substr($title, 0, 30);
        $this->tWorksheetTitle = $title;
        //文件名
        //不过滤
//        $filename = preg_replace('/[^aA-zZ0-9\_\-]/', '', $filename);
//        $this->filename = $filename;
    }
    
    /**
     * 循环生成Excel行,xml模式
     * @param array $data
     * @return string
     */
    private function addRow($data)
    {
        $cells = '';
        foreach ($data as $val) {
            $type = $this->type;
            //字符转换为 HTML 实体
            $val = htmlentities($val,ENT_COMPAT,$this->coding);
            $cells .= "<Cell><Data ss:Type=\"$type\">" . $val . "</Data></Cell>\n";
        }
        return $cells;
    }
    
    /**
     * 生成Excel文件
     * csv速度更快， 内存消耗更小
     * @param string $filename
     * @return void
     */
    public function xml_display($filename = '', $args=array(),$header_data = false)
    {
        if ($filename == '') {
            $filename = 'excel';
        };
        $this->config('utf-8', false, 'default', $filename);
        header("Content-Type: application/vnd.ms-excel; charset=" . $this->coding);
        header("Content-Disposition: inline; filename=\"" . $filename . ".xls\"");
        echo stripslashes(sprintf($this->header, $this->coding));
        echo "\n<Worksheet ss:Name=\"" . $this->tWorksheetTitle . "\">\n<Table>\n";
        if($header_data)
        {
            if ($this->fields) {
                $rows = $this->addRow($this->fields);
                echo "<Row>\n" . $rows . "</Row>\n";
            }
        }
        foreach ($args as $key => $val) {
            foreach ($val as $fkey => $fval) {
                $tmp[$fkey] = $fval;
            }
            $rows = $this->addRow($tmp);
            if ($rows) {
                echo "<Row>\n" . $rows . "</Row>\n";
            }
        }
        echo "</Table>\n</Worksheet>\n";
        echo "</Workbook>";
        exit();
    }

    /**
     * 导出excel文件
     * @param name 导出文件名
     * @param fields 要导出的字段
     * @param result 导出数据
     * @param cstring 设计字符类型的字段
     */
    public static function export_excel($name, $fields, $result = '', $cstring = '')
    {
        $ex = new \phpshow\helper\excel();
        $ex->setfields($fields);
        // $cc = array("cid");
        if ($cstring) {
            $ex->setStringFields($cstring);
        }
        $ex->setData($result);
        $ex->save("{$name}.xls", $name);
        exit();
    }
}
