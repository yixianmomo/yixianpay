<?php
namespace Yixianmomo\WxPayment;
class NotifyAlipay
{
    private $sign_type;
    private $sign;
    private $private_key_path;
    private $public_key_path;

    public function verifySign($params){
        $result = false;
        if(isset($params['sign_type'])){
            $this->sign_type = $params['sign_type'];
        }
        if(isset($params['sign'])){
            $this->sign = $params['sign'];
        }

        $result = $this->buildRequestPara($params);

        return $result;

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



    private function buildRequestPara($para_temp)
    {
        //除去待签名参数数组中的空值和签名参数
//        $para_temp['sign_type'] = strtoupper(trim($this->sign_type));
        $para_filter = $this->paraFilter($para_temp);

        //对待签名参数数组排序
        $para_sort = $this->argSort($para_filter);

        //生成签名结果

        $mysign = $this->buildRequestMysign($para_sort);

        //签名结果与签名方式加入请求提交参数组中

//        $local_sign = urlencode($mysign);
        // echo $para_sort['sign']."<br/>";

        return $mysign;
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
            if ($key == 'sign' || $key=='sign_type'  || $val == '') {
                continue;
            } else {
                $para_filter[$key] = urldecode($para[$key]);
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
     * 生成签名结果
     * @param $para_sort 已排序要签名的数组
     * return 签名结果字符串
     */
    private function buildRequestMysign($para_sort)
    {
        //把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
        $prestr = $this->createLinkstring($para_sort);
        // echo $prestr."<br/>";
        $mysign = false;
        switch (strtoupper(trim($this->sign_type))) {
            case 'MD5':
                break;
            case 'RSA':
                $mysign = $this->rsaVerify($prestr, $this->public_key_path, $this->sign);
                break;
            default:
                $mysign = false;
        }

        return $mysign;
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
     * RSA验签
     * @param $data 待签名数据
     * @param $ali_public_key_path 支付宝的公钥文件路径
     * @param $sign 要校对的的签名结果
     * return 验证结果
     */
    private function rsaVerify($data, $public_key_path, $sign)
    {
//        var_dump($data);
        $pubKey = file_get_contents($public_key_path);
        $res = openssl_get_publickey($pubKey);
        $result = (bool) openssl_verify($data, base64_decode($sign), $res);
        openssl_free_key($res);
        return $result;
    }






}