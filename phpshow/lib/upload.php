<?php
/**
 * 文件上传类
 * Created by PhpStorm.
 * User: pengyongsheng
 * Date: 2018/9/29
 * Time: 9:39 AM
 */

namespace phpshow\lib;


class upload
{
    //文件名
    public $filename = "";
    //允许上传的扩展名
    public $allow_ext = array("xls","xlsx","jpg","png","txt");
    //允许最大上传大小
    public $allow_max_size = 0;

    /**
     * upload constructor.
     */
    public function __construct($path = "")
    {
        //上传路径
        if(empty($path))
        {
            $this->path = PS_RUNTIME."/upload/";
        }
        //默认保存文件夹名称
        $date = date("Ymd");
        $this->path = $this->path."/{$date}/";
        if(!file_exists($this->path))
        {
            mkdir($this->path,'0755',true);
        }
    }

    /**
     * 单文件上传
     */
    public function single_save($file_name)
    {
        if(!empty($_FILES))
        {
            if(isset($_FILES[$file_name]))
            {
                //没有错误的情况 type
                if($_FILES[$file_name]["error"] == UPLOAD_ERR_OK)
                {
                    $tmp_name = $_FILES[$file_name]["tmp_name"];
                    $name = basename($_FILES[$file_name]["name"]);
                    $ext = $this->get_ext($name);
                    if(!in_array($ext,$this->allow_ext))
                    {
                        return \response::returnjson("-1","上传后缀格式错误!");
                    }
                    if($_FILES[$file_name]['size'] > $this->allow_max_size && $this->allow_max_size!=0)
                    {
                        return \response::returnjson("-1","超出上传大小!");
                    }
                    $save_file = $this->path.$name;
//                    echo $tmp_name.lr;echo $this->path.name.lr;
                    $data = move_uploaded_file($tmp_name,$save_file);
                    if($data)
                    {
                        return \response::returnjson("0",$data,$save_file);
                    }else{
                        return \response::returnjson("-1","保存失败");
                    }

                }
            }else{
                return \response::returnjson("-1","上传错误!",$_FILES[$file_name]["error"]);
            }
        }
    }

    /**
     * 获取文件扩展名
     * @param $file
     * @return bool|string
     */
    public function get_ext($file)
    {
        $right_offset = strrpos($file, '.');
        if($right_offset){
            $ext = substr($file, $right_offset + 1);
        }else{
            $ext = '';
        }
        return $ext;
    }

}