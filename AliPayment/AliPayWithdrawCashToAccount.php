<?php
/**
 * Created by PhpStorm.
 * User: cao 企业支付宝付款类
 * Date: 2017/9/20 0020
 * Time: 上午 10:00
 */
namespace Yixianmomo\AliPayment;

class AliPayWithdrawCashToAccount
{

    private $service = 'alipay.fund.trans.toaccount.transfer';

    private $https_url = 'https://openapi.alipay.com/gateway.do?';

    private $partner;

    private $payee_type = 'ALIPAY_LOGONID';

    private $app_id;

    private $version = '1.0';

    private $format = 'json';

    private $charset = 'UTF-8';

    private $sign_type = 'RSA';

    private $private_key_path;

    private $public_key_path;

    private $out_biz_no;

    private $amount;

    private $payee_account;

    private $key;

    private $transport;

    private $cacert;

    private $timestamp;

    private $postCharset;

    private $fileCharset;

    private $remark;

    public function __construct()
    {
        $this->cacert = getcwd() . DIRECTORY_SEPARATOR . 'cert' . DIRECTORY_SEPARATOR . 'cacert.pem';
        $this->timestamp = date('Y-m-d H:i:s');
    }

    /**
     * 取得支付链接参数
     */
    public function getPayPara()
    {
        $parameter = array(
            'sign_type' => $this->sign_type,
            'method' => $this->service,
            'timestamp' => $this->timestamp,
            'app_id' => $this->app_id,
            'charset' => $this->charset,
            'version' => $this->version,
            'format' => $this->format,
            'biz_content' => $this->get_biz_content()
        );

        $para = $this->buildRequestPara($parameter);

        return $para; //返回原生支付需要用到的参数;

    }


    protected function get_biz_content()
    {
        $biz_content = array(
            'out_biz_no' => $this->out_biz_no,
            'payee_type' => $this->payee_type,
            'payee_account' => $this->payee_account,
            'amount' => $this->amount,
            'remark' => $this->remark
        );
        return json_encode($biz_content, JSON_UNESCAPED_UNICODE);
    }

    private function setupCharsets($request)
    {
        if ($this->checkEmpty($this->postCharset)) {
            $this->postCharset = 'UTF-8';
        }
        $str = preg_match('/[\x80-\xff]/', $this->app_id) ? $this->app_id : print_r($request, true);
        $this->fileCharset = mb_detect_encoding($str, "UTF-8, GBK") == 'UTF-8' ? 'UTF-8' : 'GBK';
    }

    /**
     * 验证消息是否是支付宝发出的合法消息
     */
    public function verify()
    {
        // 判断请求是否为空
        if (empty($_POST) && empty($_GET)) {
            return false;
        }

        $data = $_POST ?: $_GET;

        // 生成签名结果
        $is_sign = $this->getSignVeryfy($data, $data['sign']);

        // 获取支付宝远程服务器ATN结果（验证是否是支付宝发来的消息）
        $response_txt = 'true';
        if (!empty($data['notify_id'])) {
            $response_txt = $this->getResponse($data['notify_id']);
        }

        // 验证
        // $response_txt的结果不是true，与服务器设置问题、合作身份者ID、notify_id一分钟失效有关
        // isSign的结果不是true，与安全校验码、请求时的参数格式（如：带自定义参数等）、编码格式有关
        if (preg_match('/true$/i', $response_txt) && $is_sign) {
            return true;
        } else {
            return false;
        }
    }

    public function setAppID($app_id)
    {
        $this->app_id = $app_id;
        return $this;
    }

    public function setRemark($remark)
    {
        $this->remark = $remark;
        return $this;
    }

    public function setPayeeAccount($payee_account)
    {
        $this->payee_account = $payee_account;
        return $this;
    }



    public function setOutBizNo($out_biz_no)
    {
        $this->out_biz_no = $out_biz_no;
        return $this;
    }

    public function setPartner($partner)
    {
        $this->partner = $partner;
        return $this;
    }

    public function setPrivateKeyPath($private_key_path)
    {
        $this->private_key_path = $private_key_path;
        return $this;
    }

    public function setPublicKeyPath($public_key_path)
    {
        $this->public_key_path = $public_key_path;
        return $this;
    }


    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    public function setSignType($sign_type)
    {
        $this->sign_type = $sign_type;
        return $this;
    }


    /**
     * 生成要请求给支付宝的参数数组
     * @param $para_temp 请求前的参数数组
     * @return 要请求的参数数组
     */
    private function buildRequestPara($para_temp)
    {
        //除去待签名参数数组中的空值和签名参数
        $para_temp['sign_type'] = strtoupper(trim($this->sign_type));
        $para_filter = $this->paraFilter($para_temp);

        //对待签名参数数组排序
        $para_sort = $this->argSort($para_filter);

        //生成签名结果

        $mysign = $this->buildRequestMysign($para_sort);

        //签名结果与签名方式加入请求提交参数组中

        $para_sort['sign'] = urlencode($mysign);
        // echo $para_sort['sign']."<br/>";

        return $para_sort;
    }

    /**
     * 生成签名结果
     * @param $para_sort 已排序要签名的数组
     * return 签名结果字符串
     */
    private function buildRequestMysign($para_sort)
    {
        //把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
        $prestr = $this->createLinkstring($para_sort);
        // echo $prestr."<br/>";
        $mysign = '';
        switch (strtoupper(trim($this->sign_type))) {
            case 'MD5':
                $mysign = $this->md5Sign($prestr, $this->key);
                break;
            case 'RSA':
                //$mysign = $this->rsaSign($prestr, trim($this->private_key_path));
                $mysign = $this->sign($prestr, $this->sign_type);
                break;
            default:
                $mysign = '';
        }

        return $mysign;
    }

    /**
     * 获取返回时的签名验证结果
     * @param $para_temp 通知返回来的参数数组
     * @param $sign 返回的签名结果
     * @return 签名验证结果
     */
    function getSignVeryfy($para_temp, $sign)
    {
        //除去待签名参数数组中的空值和签名参数
        $para_filter = $this->paraFilter($para_temp);

        //对待签名参数数组排序
        $para_sort = $this->argSort($para_filter);

        //把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
        $prestr = $this->createLinkstring($para_sort);

        $is_sgin = false;
        switch (strtoupper(trim($this->sign_type))) {
            case 'MD5':
                $is_sgin = $this->md5Verify($prestr, $sign, $this->key);
                break;
            case 'RSA':
                $is_sgin = $this->rsaVerify($prestr, $this->public_key_path, $sign);
                break;
            default:
                $is_sgin = false;
        }

        return $is_sgin;
    }

    /**
     * 除去数组中的空值和签名参数
     * @param $para 签名参数组
     * return 去掉空值与签名参数后的新签名参数组
     */
    private function paraFilter($para)
    {
        $para_filter = array();
        while ((list ($key, $val) = each($para)) == true) {
            if ($key == 'sign' || $val == '') {
                continue;
            } else {
                $para_filter[$key] = $para[$key];
            }
        }
        return $para_filter;
    }

    /**
     * 对数组排序
     * @param $para 排序前的数组
     * return 排序后的数组
     */
    private function argSort($para)
    {
        ksort($para);
        reset($para);
        return $para;
    }

    /**
     * RSA验签
     * @param $data 待签名数据
     * @param $ali_public_key_path 支付宝的公钥文件路径
     * @param $sign 要校对的的签名结果
     * return 验证结果
     */
    private function rsaVerify($data, $public_key_path, $sign)
    {
        $pubKey = file_get_contents($public_key_path);
        $res = openssl_get_publickey($pubKey);
        $result = (bool)openssl_verify($data, base64_decode($sign), $res);
        openssl_free_key($res);
        return $result;
    }

    /**
     * RSA签名
     * @param $data 待签名数据
     * @param $private_key_path 商户私钥文件路径
     * return 签名结果
     */
    private function rsaSign($data, $private_key_path)
    {
        if (file_exists($private_key_path)) {
            $priKey = file_get_contents($private_key_path);
            $res = openssl_get_privatekey($priKey);
            openssl_sign($data, $sign, $res);
            openssl_free_key($res);
            //base64编码
            $sign = base64_encode($sign);
            return $sign;
        }
    }


    protected function sign($data, $signType = "RSA")
    {


        if (!file_exists($this->private_key_path)) {
            $priKey = $this->private_key_path;
            $res = "-----BEGIN RSA PRIVATE KEY-----\n" .
                wordwrap($priKey, 64, "\n", true) .
                "\n-----END RSA PRIVATE KEY-----";
        } else {
            $priKey = file_get_contents($this->private_key_path);
            $res = openssl_get_privatekey($priKey);
        }

        ($res) or die('您使用的私钥格式错误，请检查RSA私钥配置');

        if ("RSA2" == $signType) {
            openssl_sign($data, $sign, $res, OPENSSL_ALGO_SHA256);
        } else {
            openssl_sign($data, $sign, $res);
        }

        if (file_exists($this->private_key_path)) {
            openssl_free_key($res);
        }


        $sign = base64_encode($sign);
        return $sign;
    }


    protected function checkEmpty($value)
    {
        if (!isset($value))
            return true;
        if ($value === null)
            return true;
        if (trim($value) === "")
            return true;

        return false;
    }

    /**
     * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
     * @param $para 需要拼接的数组
     * return 拼接完成以后的字符串
     */
    private function createLinkstring($para)
    {
        $arg = '';
        while ((list ($key, $val) = each($para)) == true) {
            $arg .= $key . '=' . $val . '&';
        }
        //去掉最后一个&字符
        $arg = substr($arg, 0, count($arg) - 2);

        //如果存在转义字符，那么去掉转义
        if (get_magic_quotes_gpc()) {
            $arg = stripslashes($arg);
        }

        return $arg;
    }

    /**
     * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串，并对字符串做urlencode编码
     * @param $para 需要拼接的数组
     * return 拼接完成以后的字符串
     */
    private function createLinkstringUrlencode($para)
    {
        $this->setupCharsets($para);
        //		//  如果两者编码不一致，会出现签名验签或者乱码
        if (strcasecmp($this->fileCharset, $this->postCharset)) {
            // writeLog("本地文件字符集编码与表单提交编码不一致，请务必设置成一样，属性名分别为postCharset!");
            throw new Exception("文件编码：[" . $this->fileCharset . "] 与表单提交编码：[" . $this->postCharset . "]两者不一致!");
        }

        $stringToBeSigned = "";
        $i = 0;
        foreach ($para as $k => $v) {
            if (false === $this->checkEmpty($v) && "@" != substr($v, 0, 1)) {

                // 转换成目标字符集
                $v = $this->characet($v, $this->postCharset);

                if ($i == 0) {
                    $stringToBeSigned .= "$k" . "=" . "$v";
                } else {
                    $stringToBeSigned .= "&" . "$k" . "=" . "$v";
                }
                $i++;
            }
        }

        unset ($k, $v);
        return $stringToBeSigned;
    }

    /**
     * 转换字符集编码
     * @param $data
     * @param $targetCharset
     * @return string
     */
    function characet($data, $targetCharset)
    {

        if (!empty($data)) {
            $fileType = $this->fileCharset;
            if (strcasecmp($fileType, $targetCharset) != 0) {
                $data = mb_convert_encoding($data, $targetCharset, $fileType);
                //				$data = iconv($fileType, $targetCharset.'//IGNORE', $data);
            }
        }


        return $data;
    }

    /**
     * 获取远程服务器ATN结果,验证返回URL
     * @param $notify_id 通知校验ID
     * @return 服务器ATN结果
     * 验证结果集：
     * invalid命令参数不对 出现这个错误，请检测返回处理中partner和key是否为空
     * true 返回正确信息
     * false 请检查防火墙或者是服务器阻止端口问题以及验证时间是否超过一分钟
     */
    private function getResponse($notify_id)
    {
        $transport = strtolower(trim($this->transport));
        $partner = trim($this->partner);
        $veryfy_url = '';
        if ($transport == 'https') {
            $veryfy_url = $this->__https_verify_url;
        } else {
            $veryfy_url = $this->__http_verify_url;
        }
        $veryfy_url = $veryfy_url . 'partner=' . $partner . '&notify_id=' . $notify_id;
        $response_txt = $this->getHttpResponseGET($veryfy_url);

        return $response_txt;
    }

    /**
     * 远程获取数据，GET模式
     * 注意：
     * 1.使用Crul需要修改服务器中php.ini文件的设置，找到php_curl.dll去掉前面的";"就行了
     * 2.文件夹中cacert.pem是SSL证书请保证其路径有效，目前默认路径是：getcwd().'\\cacert.pem'
     * @param $url 指定URL完整路径地址
     * @param $cacert_url 指定当前工作目录绝对路径
     * return 远程输出的数据
     */
    private function getHttpResponseGET($url)
    {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HEADER, 0); // 过滤HTTP头
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 显示输出结果
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true); //SSL证书认证
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); //严格认证
        curl_setopt($curl, CURLOPT_CAINFO, $this->cacert); //证书地址
        $responseText = curl_exec($curl);
        //var_dump( curl_error($curl) );//如果执行curl过程中出现异常，可打开此开关，以便查看异常内容
        curl_close($curl);

        return $responseText;
    }

    public function getHttpResponsePost($paramsArr)
    {
        $params = "";
        foreach($paramsArr as $k=>$v){
            $params.= $k.'='.$v."&";
        }
        $params = rtrim($params,"&");
        $ch = curl_init();
        curl_setopt($ch , CURLOPT_URL , $this->https_url);
        curl_setopt($ch , CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); // https请求 不验证证书和hosts
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); //严格认证
        curl_setopt($ch, CURLOPT_CAINFO, $this->cacert); //证书地址
        curl_setopt($ch , CURLOPT_POST, 1);
        curl_setopt($ch , CURLOPT_POSTFIELDS,$params);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }


}