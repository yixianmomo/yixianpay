<?php
/**
 * Created by PhpStorm.
 * User: caoyixian
 * Date: 2017/5/24
 * Time: 11:24
 */
namespace Yixianmomo\WxPayment;

class WxCallBack{

    protected $status;
    protected $xml;
    protected $values;
    protected $pay_key;
    public function __construct($xml,$pay_key)
    {
        $this->status = false;
        $this->xml = $xml;
        $this->pay_key = $pay_key;
        $this->FromXml();
    }
    //验证签名结果
    public function verify()
    {


        //var_dump($parse);
        $sign = $this->CreateSign();
        if ($this->values['result_code'] == 'SUCCESS' && $this->values['sign'] == $sign) {
            $this->status = true;
        }
        return $this->status;
    }


    public function getValues(){

        return $this->values;

    }

    //回复微信服务器结果
    public function replyWxService(){

        $reply = new WxPayNotifyReply();

        if($this->status == true){
            $reply->SetReturn_code("SUCCESS");
            $reply->SetReturn_msg("OK");
            //自主处理逻辑
            //$this->curlpost($url,$parsetoarray);
        } else {
            $reply->SetReturn_code("FAIL");
            $reply->SetReturn_msg("SIGN ERROR");
        }
       //把结果通知微信充值服务器
        return $reply->ToXml();

    }

    /**
     * 将xml转为array
     * @param string $xml
     * @throws WxPayException
     */
    public function FromXml()
    {
        if(!$this->xml){
            throw new WxPayException("xml数据异常！");
        }
        //将XML转为array
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $this->values = json_decode(json_encode(simplexml_load_string($this->xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $this->values;
    }


    /**
     * 生成签名
     * @return 签名，本函数不覆盖sign成员变量，如要设置签名需要调用SetSign方法赋值
     */
    public function CreateSign()
    {
        //签名步骤一：按字典序排序参数
        $xmlArray = $this->values;
        ksort($xmlArray);
        $string = $this->ToUrlParams($xmlArray);
        //签名步骤二：在string后加入KEY
        $string = $string . "&key=".$this->pay_key;
        //签名步骤三：MD5加密
        $string = md5($string);
        //签名步骤四：所有字符转为大写
        $result = strtoupper($string);
        return $result;
    }

    /**
     * 格式化参数格式化成url参数
     */
    public function ToUrlParams($xmlArray)
    {
        $buff = "";
        foreach ($xmlArray as $k => $v)
        {
            if($k != "sign" && $v != "" && !is_array($v)){
                $buff .= $k . "=" . $v . "&";
            }
        }

        $buff = trim($buff, "&");
        return $buff;
    }





    /**
     * 模拟实现post调用
     * @param unknown $url
     * @param unknown $data
     */
    private function curlpost($url, $data) {
        $process = curl_init ( $url );
        curl_setopt ( $process, CURLOPT_HEADER, 0 );
        curl_setopt ( $process, CURLOPT_TIMEOUT, 120 );
        curl_setopt($process, CURLOPT_CONNECTTIMEOUT, 120);
        if ( is_array($data) ) {
            foreach( $data as $key => $d ) {
                $param[] = $key."=".$d;
            }
            $params = implode('&', $param);
            curl_setopt ( $process, CURLOPT_POSTFIELDS, $params );
        }
        else {
            curl_setopt ( $process, CURLOPT_POSTFIELDS, $data );
        }
        curl_setopt ( $process, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt ( $process, CURLOPT_FOLLOWLOCATION, 1 );
        curl_setopt ( $process, CURLOPT_POST, 1 );
        set_time_limit(120);
        $return = curl_exec ( $process );
        curl_close ( $process );
        //需要监控处理结果，便于控制对微信支付接口的回应
        return $return;
    }







}