<?php
/**
 * Created by PhpStorm.
 * User: pengyongsheng
 * Date: 2018/11/23
 * Time: 10:04 AM
 */

namespace phpshow\helper;
use phpshow\lib\http;

class qcloud
{
    private $appid;
    private $appsecret;
    public function __construct($appid,$appsecret)
    {
        $this->appid = $appid;
        $this->appsecret = $appsecret;
    }
    /**
     * 发送短信业务
     */
    public function sms($phone,$appid,$templateid)
    {
        //先测试，后修改
        $domain = "sms.tencentcloudapi.com";
        $url = "https://{$domain}/";
        $params['Action'] = "SendSms";
        $params['Version'] = "2019-07-11";
        $params['TemplateID'] = $templateid;
        $params['SmsSdkAppid'] = $appid;  
        $params["PhoneNumberSet.0"] = $phone;
        // $params["TemplateParamSet.0"] = "";
        // $params["SessionContext"] = "";

        $params = $this->common_param($params);
//公共参数
        $keystr = $this->makeSignPlainText($params,"GET",$domain,'/');
        $params['Signature'] = $this->sign($keystr);

        $url .= '?'.http_build_query($params);
//        echo $url.lr;
//        $result = \http::post($url,$params);
        $result = http::get($url);
        return $result;
    }

    /**
     * 获取指标参数
     */
    public function monitor_Metrics()
    {

        $domain = "monitor.tencentcloudapi.com";
        $url = "https://{$domain}/";
        $params['Action'] = "DescribeBaseMetrics";
        $params['Version'] = "2018-07-24";
        $params['Region'] = "ap-guangzhou";
        $params['Namespace'] = "QCE/CVM";

        $params = $this->common_param($params);
//公共参数
        $keystr = $this->makeSignPlainText($params,"GET",$domain,'/');
        $params['Signature'] = $this->sign($keystr);

        $url .= '?'.http_build_query($params);
//        echo $url.lr;
//        $result = \http::post($url,$params);
        $result = http::get($url);
        return $result;
    }

    /**
     * 监控
     * 主要关心参数CPUUsage|MemUsage
     * @return mixed
     */
    public function monitor($server = [],$metricname = 'CPUUsage')
    {
        $domain = "monitor.tencentcloudapi.com";
        $url = "https://{$domain}/";
        $params['Action'] = "GetMonitorData";
        $params['Version'] = "2018-07-24";
        $params['Region'] = "ap-guangzhou";
        $params['Namespace'] = "QCE/CVM";
        $params['MetricName'] = $metricname;
        //循环出规则
        foreach($server as $key=>$val)
        {
            $params["Instances.0.Dimensions.0.Name"] = "InstanceId";
            $params["Instances.0.Dimensions.0.Value"] = $val;
        }

        $params = $this->common_param($params);
//公共参数
        $keystr = $this->makeSignPlainText($params,"GET",$domain,'/');
        $params['Signature'] = $this->sign($keystr);

        $url .= '?'.http_build_query($params);
//        echo $url.lr;
//        $result = \http::post($url,$params);
        $result = http::get($url);
        return $result;


    }

    //-----------------------balancer监听器---------------------------

    /**
     * 查看应用型的负载均衡
     * @param $name
     * @return mixed
     */
    public function balancer_look()
    {
        $domain = "clb.tencentcloudapi.com";
        $url = "https://{$domain}/";
        $params['Action'] = "DescribeLoadBalancers";
        $params['Version'] = "2018-03-17";
        $params['Region'] = "ap-guangzhou";
        $params['Limit'] = "40";
//        $params['LoadBalancerId'] = $name;

        $params = $this->common_param($params);
//公共参数
        $keystr = $this->makeSignPlainText($params,"GET",$domain,'/');
        $params['Signature'] = $this->sign($keystr);

        $url .= '?'.http_build_query($params);
        $result = http::get($url);
        return $result;
    }

    /**
     * 查看监听器id
     * @param $listener
     * @return mixed
     */
    public function balancer_listener($listener)
    {
        $domain = "clb.tencentcloudapi.com";
        $url = "https://{$domain}/";
        $params['Action'] = "DescribeListeners";
        $params['Version'] = "2018-03-17";
        $params['Region'] = "ap-guangzhou";

        $params['LoadBalancerId'] = $listener;

        $params = $this->common_param($params);
//公共参数
        $keystr = $this->makeSignPlainText($params,"GET",$domain,'/');
        $params['Signature'] = $this->sign($keystr);

        $url .= '?'.http_build_query($params);
//        echo $url.lr;
        $result = http::get($url);
        return $result;
    }

    /**
     * 规则绑定监听器
     * @param string $name
     * @param string $listen_name
     * @param array $rule
     * @return mixed
     */
    public function balancer_RegisterTargets($name = "",$listen_name= "",$domain_str="",$url_str="/",$InstanceId ="")
    {

        $domain = "clb.tencentcloudapi.com";
        $url = "https://{$domain}/";
        $params['Action'] = "RegisterTargets";
        $params['Version'] = "2018-03-17";
        $params['Region'] = "ap-guangzhou";
        $params['LoadBalancerId'] = $name;
        $params['ListenerId'] = $listen_name;
        //locationid或domain兼url 选填
//        $params['LocationId'] = $locid;

        $params['Domain'] = $domain_str;
        $params['Url'] = $url_str;

        //要注册的后端机器列表只做绑定80端口的就ok了
        $params['Targets.0.InstanceId'] = $InstanceId;
        $params['Targets.0.Port'] = 80;
        /*
        foreach($targets as $key=>$val)
        {
            //应用id
            $params['Targets.'.$key.'.InstanceId'] = $val['InstanceId'];
            //应用端口
            $params['Targets.'.$key.'.Port'] = $val['Port'];
//            $params['Targets.'.$key.'.Weight'] = $val['Weight'];
        }
        */


        $params = $this->common_param($params);
//公共参数
        $keystr = $this->makeSignPlainText($params,"GET",$domain,'/');
        $params['Signature'] = $this->sign($keystr);

        $url .= '?'.http_build_query($params);
//        echo $url.lr;
//        $result = \http::post($url,$params);
        $result = http::get($url);
        return $result;


    }



    /**
     * 创建规则
     * @param string $name
     * @return mixed
     * {
    "Response": {
    "RequestId": "3d62c322-23ee-4732-9908-cf86c254c277"
    }
    }
     */
    public function balancer_CreateRule($name = "",$listen_name= "",$rule = [])
    {

        $domain = "clb.tencentcloudapi.com";
        $url = "https://{$domain}/";
        $params['Action'] = "CreateRule";
        $params['Version'] = "2018-03-17";
        $params['Region'] = "ap-guangzhou";
        $params['LoadBalancerId'] = $name;
        $params['ListenerId'] = $listen_name;
        //循环出规则
        foreach($rule as $key=>$val)
        {
            $params['Rules.'.$key.'.Domain'] = $val['Domain'];
            $params['Rules.'.$key.'.Url'] = $val['Url'];
        }

        $params = $this->common_param($params);
//公共参数
        $keystr = $this->makeSignPlainText($params,"GET",$domain,'/');
        $params['Signature'] = $this->sign($keystr);

        $url .= '?'.http_build_query($params);
//        echo $url.lr;
//        $result = \http::post($url,$params);
        $result = http::get($url);
        return $result;


    }


    /**
     * 创建负载均衡监听器
     * @param string $name
     * @return mixed
     * {
    "Response": {
    "ListenerIds": [
    "lbl-hp4gihri"
    ],
    "RequestId": "4ceecb11-0d27-4e82-872d-feaf3c077539"
    }
    }
     */
    public function balancer_CreateListener($name = "")
    {
        $domain = "clb.tencentcloudapi.com";
//        $domain = "clb.ap-guangzhou.tencentcloudapi.com";
        $url = "https://{$domain}/";
        $params['Action'] = "CreateListener";
        $params['Version'] = "2018-03-17";
        $params['Region'] = "ap-guangzhou";
        $params['LoadBalancerId'] = $name;
        $params['Ports.0'] = "80";
        $params['ListenerNames.0'] = "com";
        $params['Protocol'] = "HTTP";
        $params = $this->common_param($params);
//公共参数
        $keystr = $this->makeSignPlainText($params,"GET",$domain,'/');
        $params['Signature'] = $this->sign($keystr);

        $url .= '?'.http_build_query($params);
//        echo $url.lr;
//        $result = \http::post($url,$params);
        $result = http::get($url);
        return $result;


    }

    /**
     * 负载均衡的购买
     * 这个接口没有/v2/index.php
     * @param string $name
     * @return mixed
     * { "Response": { "LoadBalancerIds": [ "lb-89itav8h" ], "RequestId": "492dac83-f527-4e3b-9afc-dffdfaba2c80" } }
     */
    public function balancer_buy($name = "test")
    {
        $domain = "clb.tencentcloudapi.com";
//        $domain = "clb.ap-guangzhou.tencentcloudapi.com";
        $url = "https://{$domain}/";
        $params['Action'] = "CreateLoadBalancer";
        $params['Version'] = "2018-03-17";
        $params['Forward'] = '1';
        $params['Region'] = "ap-guangzhou";
        $params['LoadBalancerType'] = "OPEN";
        $params['LoadBalancerName'] = $name;
        $params['RequestClient'] = "SDK_PHP_3.0.35";
        $params = $this->common_param($params);
//公共参数
//        $params['SignatureMethod'] = "HmacSHA256";
        $keystr = $this->makeSignPlainText($params,"GET",$domain,'/');
        $params['Signature'] = $this->sign($keystr);

        $url .= '?'.http_build_query($params);
//        echo $url.lr;
//        $result = \http::post($url,$params);
        $result = http::get($url);
        return $result;


    }

    /**
     * 域名列表
     *
     */
    public function domainrecord($domain_name)
    {
        $domain = "cns.api.qcloud.com";
        $url = "https://{$domain}/v2/index.php";
        $params['Action'] = "RecordList";
        $params['domain'] = $domain_name;
        $params['offset'] = 0;
        $params['length'] = 20;
        $params = $this->common_param($params);
//公共参数
        $keystr = $this->makeSignPlainText($params,"POST",$domain);
        $params['Signature'] = $this->sign($keystr);
        $result = http::post($url,$params);
        return $result;
    }

    /**
     * 域名列表
     *
     */
    public function domainlist()
    {
        $domain = "cns.api.qcloud.com";
        $url = "https://{$domain}/v2/index.php";
        $params['Action'] = "DomainList";
        $params['offset'] = 0;
        $params['length'] = 10;
        $params = $this->common_param($params);
//公共参数
        $keystr = $this->makeSignPlainText($params,"POST",$domain);
        $params['Signature'] = $this->sign($keystr);
        $result = http::post($url,$params);
        return $result;
    }

    /**
     * 域名解析
     * @param $domain
     * @param $subdomain
     * @param $value
     * @return mixed
     */
    public function domainAddRecord($gdomain,$subdomain,$value)
    {
        $domain = "cns.api.qcloud.com";
        $url = "https://{$domain}/v2/index.php";
        $params['Action'] = "RecordCreate";
        $params['domain'] = $gdomain;  //根域名
        $params['subDomain'] = $subdomain;  //添加子域名
        $params['recordType'] = "A";  //"A", "CNAME", "MX", "TXT", "NS", "AAAA", "SRV"
        $params['recordLine'] = "默认";
        $params['value'] = $value;  //添加解析的值

        $params = $this->common_param($params);
        $keystr = $this->makeSignPlainText($params,"POST",$domain);
        $params['Signature'] = $this->sign($keystr);
        $result = http::post($url,$params);
        return $result;

    }


    //---------------------验证方法----------------------

    /**
     * 公共参数
     */
    public function common_param($params)
    {
        $params['SecretId'] = $this->appid;
//        $params['Nonce'] = uniqid("nonce");
        $params['Nonce'] = time().rand(1,99);
        $params['Timestamp'] = time();
//        $params['SignatureMethod'] = "HmacSHA256";
        return $params;
    }


    /**
     * sign
     * 生成签名
     * @param  string $srcStr    拼接签名源文字符串
     * @param  string $secretKey secretKey
     * @param  string $method    请求方法
     * @return
     */
    public function sign($srcStr, $method = 'HmacSHA1')
    {

        switch ($method) {
            case 'HmacSHA1':
                $retStr = base64_encode(hash_hmac('sha1', $srcStr, $this->appsecret, true));
                break;
            case 'HmacSHA256':
                $retStr = base64_encode(hash_hmac('sha256', $srcStr, $this->appsecret, true));
                break;
            default:
                throw new \Exception($method . ' is not a supported encrypt method');
                return false;
                break;
        }

        return $retStr;
    }



    /**
     * makeSignPlainText
     * 生成拼接签名源文字符串
     * @param  array $requestParams  请求参数
     * @param  string $requestMethod 请求方法
     * @param  string $requestHost   接口域名
     * @param  string $requestPath   url路径
     * @return
     */
    public function makeSignPlainText($requestParams,
                                             $requestMethod = 'GET', $requestHost = YUNAPI_URL,
                                             $requestPath = '/v2/index.php')
    {

        $url = $requestHost . $requestPath;

        // 取出所有的参数
        $paramStr = $this->_buildParamStr($requestParams, $requestMethod);

        $plainText = $requestMethod . $url . $paramStr;

        return $plainText;
    }

    private function formatSignString($host, $uri, $param, $requestMethod = 'GET')
    {
        $tmpParam = [];
        ksort($param);
        foreach ($param as $key => $value) {
            array_push($tmpParam, str_replace("_",".",$key) . "=" . $value);
        }
        $strParam = join ("&", $tmpParam);
        $signStr = strtoupper($requestMethod) . $host . $uri ."?".$strParam;
        return $signStr;
    }

    /**
     * _buildParamStr
     * 拼接参数
     * @param  array $requestParams  请求参数
     * @param  string $requestMethod 请求方法
     * @return
     */
    protected function _buildParamStr($requestParams, $requestMethod = 'GET')
    {
        $paramStr = '';
        ksort($requestParams);
        $i = 0;
        foreach ($requestParams as $key => $value)
        {
            if ($key == 'Signature')
            {
                continue;
            }
            // 排除上传文件的参数
            if ($requestMethod == 'POST' && substr($value, 0, 1) == '@') {
                continue;
            }
            // 把 参数中的 _ 替换成 .
            if (strpos($key, '_'))
            {
                $key = str_replace('_', '.', $key);
            }

            if ($i == 0)
            {
                $paramStr .= '?';
            }
            else
            {
                $paramStr .= '&';
            }
            $paramStr .= $key . '=' . $value;
            ++$i;
        }

        return $paramStr;
    }
}