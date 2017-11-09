<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/14 0014
 * Time: 下午 9:04
 */

namespace Yixianmomo\WxPayment;


class WxOauth2
{

    /*
    * 网页授权，获取access_token，非网页获取用户信息
    * cao，2016-04-11
    */
    public function getPageAccessToken($appid,$app_secret,$code) {
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid={$appid}&secret={$app_secret}&code={$code}&grant_type=authorization_code";
        $res = json_decode ( $this->httpRequest ( $url ) ,true);
        return $res;
    }


    public function getPageUserInfo($access_token,$openid){
        $url = "https://api.weixin.qq.com/sns/userinfo?access_token={$access_token}&openid={$openid}&lang=zh_CN";
        $res = json_decode ( $this->httpRequest ( $url ) ,true);
        //如果是未关注用户，上述方法得不到用户更详细的信息，必须采用其它方式
        return $res;
    }

    public function httpRequest($url,$data = null){
        $curl = curl_init();
        curl_setopt ( $curl, CURLOPT_URL, $url );
        curl_setopt ( $curl, CURLOPT_SSL_VERIFYPEER, false );
        curl_setopt ( $curl, CURLOPT_SSL_VERIFYHOST, false );
        if(isset($data)){
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }



}