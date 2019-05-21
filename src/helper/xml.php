<?php
namespace phpshow\helper;
/**
 * xml操作类
 * @Author:show
 */
class xml
{
    public $dom;
    public function __construct()
    {
        $this->dom = new \DOMDocument();
        $this->dom->formatOutput = true ;  
        
    }

    //==================解释xml====================
    /**
     * @desc 解释xml
     * @param xml xml字符
     * @param section 需要解释的块
     */
    public function load($xml,$section)
    {
        $this->dom->loadXML($xml);
        $params = $this->dom->getElementsBytagName($section);
        foreach($params as $param)
        {
            $headline = array();
            if($param->childNodes->length){
                foreach($param->childNodes as $i)
                {
                    $headline[$i->nodeName] = $i->nodeValue;
                }
            }
            $headlines[] = $headline;
        }
        // var_dump($headlines);
        return $headlines;
    }
    /**
	 * 提取出xml数据包中的加密消息
	 * @param string $xmltext 待提取的xml字符串
	 * @return string 提取出的加密消息字符串
	 */
	public function extract($xmltext)
	{
		try {
			$xml = new \DOMDocument();
			$xml->loadXML($xmltext);
			$array_e = $xml->getElementsByTagName('Encrypt');
			$array_a = $xml->getElementsByTagName('ToUserName');
			$encrypt = $array_e->item(0)->nodeValue;
			$tousername = $array_a->item(0)->nodeValue;
			return array(0, $encrypt, $tousername);
		} catch (Exception $e) {
			//print $e . "\n";
			return array(ErrorCode::$ParseXmlError, null, null);
		}
	}

}
