<?php

/*
 * This file is part of the overtrue/wechat.
 *
 * (c) overtrue <i@overtrue.me>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * Application.php.
 *
 * Part of Overtrue\WeChat.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author    overtrue <i@overtrue.me>
 * @copyright 2015
 *
 * @see      https://github.com/overtrue
 * @see      http://overtrue.me
 */

namespace Yixianmomo\WxChat;

class WxInterface
{

    const TYPE_TEXT = 0;
    const TYPE_IMAGE = 1;
    const TYPE_VOICE = 2;
    const TYPE_VIDEO = 3;
    const TYPE_THUMB = 4;
    const TYPE_NEWS = 5;
    private $appId;
    private $appSecret;
    private $token;
    public function __construct() {
        $this->appId = config('params.wx_config.appId');
        $this->appSecret = config('params.wx_config.appSecret');
        $this->token = config('params.wx_config.token');
    }

    //返回素材类型值
   public static function getMsgType($typeId){
       $news_type = [
           self::TYPE_TEXT => 'text',
           self::TYPE_IMAGE => 'image',
           self::TYPE_VOICE => 'voice',
           self::TYPE_VIDEO => 'video',
           self::TYPE_THUMB => 'thumb',
           self::TYPE_NEWS => 'news'
       ];
       return $news_type[$typeId];
   }


    //用户管理相关
    //设定用户标签
    public function createUserTag($access_token,$name){
      $url = "https://api.weixin.qq.com/cgi-bin/tags/create?access_token=".$access_token;
      $data = ['tag'=>['name'=>$name]];
      $data = json_encode($data,JSON_UNESCAPED_UNICODE);
      $json = $this->httpRequest($url,$data);
      $response = json_decode ( $json, true );
      return $response;
    }

    public function getUserTag($access_token){
       $url = "https://api.weixin.qq.com/cgi-bin/tags/get?access_token={$access_token}";
        $json = $this->httpRequest($url);
        $response = json_decode ( $json, true );
        return $response;
    }

    public function updateUserTag($access_token,$tag_id,$tag_name){

        $url = "https://api.weixin.qq.com/cgi-bin/tags/update?access_token={$access_token}";
        $data = ['tag'=>['id'=>$tag_id,'name'=>$tag_name]];
        $data = json_encode($data,JSON_UNESCAPED_UNICODE);
        $json = $this->httpRequest($url,$data);
        $response = json_decode ( $json, true );
        return $response;

    }

    public function deleteUserTag($access_token,$tag_id){

        $url = "https://api.weixin.qq.com/cgi-bin/tags/delete?access_token={$access_token}";
        $data = ['tag'=>['id'=>$tag_id]];
        $data = json_encode($data);
        $json = $this->httpRequest($url,$data);
        $response = json_decode ( $json, true );
        return $response;

    }

    public function getTagUserList($access_token,$tag_id,$next_openid=null){
        $url = "https://api.weixin.qq.com/cgi-bin/user/tag/get?access_token={$access_token}";
        if(isset($next_openid)){
            $data = ['tagid'=>$tag_id,'next_openid'=>$next_openid];
        }else{
            $data = ['tagid'=>$tag_id];
        }

        $data = json_encode($data);
        $json = $this->httpRequest($url,$data);
        $response = json_decode ( $json, true );
        return $response;

    }

    public function setBatchTagWithUser($access_token,$openid_list,$tagid){
        $url= "https://api.weixin.qq.com/cgi-bin/tags/members/batchtagging?access_token={$access_token}";
        $data = ['openid_list'=>$openid_list,'tagid'=>$tagid];
        $data = json_encode($data);
        $json = $this->httpRequest($url,$data);
        $response = json_decode ( $json, true );
        return $response;

    }

    public function cancelBatchTagWithUser($access_token,$openid_list,$tagid){
        $url= "https://api.weixin.qq.com/cgi-bin/tags/members/batchuntagging?access_token={$access_token}";
        $data = ['openid_list'=>$openid_list,'tagid'=>$tagid];
        $data = json_encode($data);
        $json = $this->httpRequest($url,$data);
        $response = json_decode ( $json, true );
        return $response;
    }




    public function getUserList($access_token,$nextOpenId = null){
        if(isset($nextOpenId)){
            $url = "https://api.weixin.qq.com/cgi-bin/user/get?access_token=".$access_token."&next_openid=".$nextOpenId;
        }else{
            $url = "https://api.weixin.qq.com/cgi-bin/user/get?access_token=".$access_token."&next_openid=";
        }

        $json = $this->httpRequest ( $url );
        $userlist = json_decode ( $json, true );
        return $userlist;
    }

    public function getUserInfo($access_token,$openid) {
        $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=" . $access_token . "&openid=" . $openid . "&lang=zh_CN";
        $json = $this->httpRequest ( $url ); // file_get_contents ( $url ); // 获取微信用户基本信息
        $userInfo = json_decode ( $json, true );
        return $userInfo;
    }

    public function getUserBlackList($access_token,$nextOpenId = null){

        $url = "https://api.weixin.qq.com/cgi-bin/tags/members/getblacklist?access_token={$access_token}";
        $data = ['begin_openid'=>$nextOpenId];
        $data = json_encode($data);
        $json = $this->httpRequest ($url,$data);
        $response = json_decode ( $json, true );
        return $response;

    }
    //拉黑
    public function batchblackUserList($access_token,$openid_list){
        //openid_list = ["aaaa","bbbb"]
        $url = "https://api.weixin.qq.com/cgi-bin/tags/members/batchblacklist?access_token={$access_token}";
        $data = ['openid_list'=>$openid_list];
        $data = json_encode($data);
        $json = $this->httpRequest ($url,$data);
        $response = json_decode ( $json, true );
        return $response;

    }

    //取消拉黑
    public function cancelblackUserList($access_token,$openid_list){
        //openid_list = ["aaaa","bbbb"]
        $url = "https://api.weixin.qq.com/cgi-bin/tags/members/batchunblacklist?access_token={$access_token}";
        $data = ['openid_list'=>$openid_list];
        $data = json_encode($data);
        $json = $this->httpRequest ($url,$data);
        $response = json_decode ( $json, true );
        return $response;

    }

    //设置用户备注
    public function setUserRemark($access_token,$openid,$remark){

        $url = "https://api.weixin.qq.com/cgi-bin/user/info/updateremark?access_token={$access_token}";
        $data = ['openid'=>$openid,'remark'=>$remark];
        $data = json_encode($data,JSON_UNESCAPED_UNICODE);
        $json = $this->httpRequest($url,$data);
        $response = json_decode ( $json, true );
        return $response;

    }


    /*
     * 客服相关
     * cao
     * 2017-10-14
     */
    //获取客服列表
    public function getKfUserList($access_token){
        $url = "https://api.weixin.qq.com/cgi-bin/customservice/getkflist?access_token=".$access_token;
        $json = $this->httpRequest ( $url);
        $response = json_decode ( $json, true );
        return $response;
    }
   //获取在线客服
    public function getKfOnlineList($access_token){
        $url = "https://api.weixin.qq.com/cgi-bin/customservice/getonlinekflist?access_token={$access_token}";
        $json = $this->httpRequest ( $url);
        $response = json_decode ( $json, true );
        return $response;
    }
    //添加客服帐号
    public function addNewKf($access_token,$kf_account,$nickname){
        $url = "https://api.weixin.qq.com/customservice/kfaccount/add?access_token={$access_token}";
        $data = ['kf_account'=>$kf_account,'nickname'=>$nickname];
        $data = json_encode($data,JSON_UNESCAPED_UNICODE);
        $json = $this->httpRequest ( $url,$data);
        $response = json_decode ( $json, true );
        return $response;

    }

    //邀请绑定客服帐号
    public function bindKfAccount($access_token,$kf_account,$invite_wx){

        $url = "https://api.weixin.qq.com/customservice/kfaccount/inviteworker?access_token={$access_token}";
        $data = ['kf_account'=>$kf_account,'invite_wx'=>$invite_wx];
        $data = json_encode($data);
        $json = $this->httpRequest ( $url,$data);
        $response = json_decode ( $json, true );
        return $response;

    }

    //更新客服信息
    public function updateKfAccount($access_token,$kf_account,$nickname){
        $url = "https://api.weixin.qq.com/customservice/kfaccount/update?access_token={$access_token}";
        $data = ['kf_account'=>$kf_account,'nickname'=>$nickname];
        $data = json_encode($data,JSON_UNESCAPED_UNICODE);
        $json = $this->httpRequest ( $url,$data);
        $response = json_decode ( $json, true );
        return $response;
    }

    //删除客服帐号
    public function deleteKfAccount($access_token,$kf_account){
        $url = "https://api.weixin.qq.com/customservice/kfaccount/del?access_token={$access_token}&kf_account={$kf_account}";
        $json = $this->httpRequest ( $url);
        $response = json_decode ( $json, true );
        return $response;
    }

    public function uploadKfHeadImg($access_token,$kf_account,$filepath){
        $url = "https://api.weixin.qq.com/customservice/kfaccount/uploadheadimg?access_token={$access_token}&kf_account={$kf_account}";
        $json = $this->uploadBinaryFile($url,$filepath);
        $response = json_decode ( $json, true );
        return $response;

    }


    //创建会话
    public function createKfSession($access_token,$kf_account,$openid){
        $url = "https://api.weixin.qq.com/customservice/kfsession/create?access_token={$access_token}";
        $data = ['kf_account'=>$kf_account,'openid'=>$openid];
        $data = json_encode($data);
        $json = $this->httpRequest ( $url,$data);
        $response = json_decode ( $json, true );
        return $response;
    }

    //关闭会话
    public function closeKfSession($access_token,$kf_account,$openid){
        $url = "https://api.weixin.qq.com/customservice/kfsession/close?access_token={$access_token}";
        $data = ['kf_account'=>$kf_account,'openid'=>$openid];
        $data = json_encode($data);
        $json = $this->httpRequest ( $url,$data);
        $response = json_decode ( $json, true );
        return $response;
    }

    //获取客户会话状态
    public function getKfSession($access_token,$openid){
        $url = "https://api.weixin.qq.com/customservice/kfsession/getsession?access_token={$access_token}&openid={$openid}";
        $json = $this->httpRequest ( $url);
        $response = json_decode ( $json, true );
        return $response;
    }

    //获取客服会话列表
    public function getKfSessionList($access_token,$kf_account){
        $url = "https://api.weixin.qq.com/customservice/kfsession/getsessionlist?access_token={$access_token}&kf_account={$kf_account}";
        $json = $this->httpRequest ( $url);
        $response = json_decode ( $json, true );
        return $response;
    }


    //通过kf接口给用户发信息
    public function sendMsgToUserWithKf($access_token,$touser,$content,$type='text'){
        $url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=".$access_token;
        $senddata = ['touser'=>$touser,'msgtype'=>$type,'text'=>['content'=>$content]];
        $data = json_encode($senddata,JSON_UNESCAPED_UNICODE);
        //return $data;
        $json = $this->httpRequest ( $url,$data);
        $result = json_decode ( $json, true );
        return $result;
    }

    //获取客服聊天记录
    public function getKfChatMsgList($access_token,$starttime,$endtime,$msgid=1,$number=1000){
        $url = "https://api.weixin.qq.com/customservice/msgrecord/getmsglist?access_token={$access_token}";
        $senddata = ['starttime'=>$starttime,'endtime'=>$endtime,'msgid'=>$msgid,'number'=>$number];
        $data = json_encode($senddata);
        //return $data;
        $json = $this->httpRequest ( $url,$data);
        $result = json_decode ( $json, true );
        return $result;
    }

    /*
    * 菜单相关
    * cao
    * 2017-10-14
    */

    public function createMenu($access_token,$menudata){

        $url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=".$access_token;
        $menudata = json_encode($menudata,JSON_UNESCAPED_UNICODE);
        $json = $this->httpRequest ($url,$menudata);
        $response = json_decode ( $json, true );
        return $response;

    }


    public function getMenu($access_token){
        $url = "https://api.weixin.qq.com/cgi-bin/menu/get?access_token=".$access_token;
        $json = $this->httpRequest ( $url );
        $menu = json_decode ( $json, true );
        return $menu;

    }

    public function deleteMenu($access_token){
        $url = "https://api.weixin.qq.com/cgi-bin/menu/delete?access_token={$access_token}";
        $json = $this->httpRequest ( $url );
        $response = json_decode ( $json, true );
        return $response;

    }

    /*
     * 微信素材管理,所有素材均调用curl以二进制方式上传
     * cao
     * 2017-10-14
     */
    //媒体文件类型，分别有图片（image）、语音（voice）、视频（video）和缩略图（thumb）
    //新增临时素材
    public function addMediaUploadWithTemp($access_token,$filepath,$midea_type = 'image'){
        $url = "https://api.weixin.qq.com/cgi-bin/media/upload?access_token={$access_token}&type={$midea_type}";
        $json = $this->uploadBinaryFile($url,$filepath);
        $response = json_decode ( $json, true );
        return $response;
    }
    //获取临时素材
    public function getMediaWithTemp($access_token,$midea_id){
        $url = "https://api.weixin.qq.com/cgi-bin/media/get?access_token={$access_token}&media_id={$midea_id}";
        $json = $this->httpRequest($url);
        $response = json_decode ( $json, true );
        return $response;
    }
    //上传图文消息内的图片获取URL
    public function uploadArticleImage($access_token,$filepath){
        $url = "https://api.weixin.qq.com/cgi-bin/media/uploadimg?access_token={$access_token}";
        $json = $this->uploadBinaryFile($url,$filepath);
        $response = json_decode ( $json, true );
        return $response;
    }
    //新增永久素材
    public function addMediaUploadWithMaterial($access_token,$filepath,$midea_type = 'image'){
        $url = "https://api.weixin.qq.com/cgi-bin/material/add_material?access_token={$access_token}&type={$midea_type}";
        $json = $this->uploadBinaryFile($url,$filepath);
        $response = json_decode ( $json, true );
        return $response;
    }
    //新建永久图文素材
    public function addArticleWithMatrial($access_token,$article_list)
    {
        $url = "https://api.weixin.qq.com/cgi-bin/material/add_news?access_token={$access_token}";
        $article_list = json_encode($article_list,JSON_UNESCAPED_UNICODE);
        $json = $this->httpRequest($url,$article_list);
        $response = json_decode ( $json, true );
        return $response;

    }
    //获取永久图文素材
    public function getArticleWithMatrial($access_token,$midea_id){

        $url = "https://api.weixin.qq.com/cgi-bin/material/get_material?access_token={$access_token}";
        $data = ['media_id'=>$midea_id];
        $data = json_encode($data);
        $json = $this->httpRequest($url,$data);
        $response = json_decode ( $json, true );
        return $response;

    }

    //删除永久图文素材
    public function deleteMediaWithMatrial($access_token,$midea_id){
        $url = "https://api.weixin.qq.com/cgi-bin/material/del_material?access_token={$access_token}";
        $data = ['media_id'=>$midea_id];
        $data = json_encode($data);
        $json = $this->httpRequest($url,$data);
        $response = json_decode ( $json, true );
        return $response;
    }

    //获取永久图文素材总数
    public function getMediaCountWithMatrial($access_token){
        $url = "https://api.weixin.qq.com/cgi-bin/material/get_materialcount?access_token={$access_token}";
        $json = $this->httpRequest($url);
        $response = json_decode ( $json, true );
        return $response;
    }

    //获取素材列表
    public function getMediaListWithMatrial($access_token,$type,$offset=0,$count=10){

        $url = "https://api.weixin.qq.com/cgi-bin/material/batchget_material?access_token={$access_token}";

        $data = ['type'=>$type,'offset'=>$offset,'count'=>$count];
        $data = json_encode($data);
        $json = $this->httpRequest($url,$data);
        $response = json_decode ( $json, true );
        return $response;


    }


    public function sendMsgToAllUser($access_token,$media_id,$msgtype,$tag_id=null){

        $url = "https://api.weixin.qq.com/cgi-bin/message/mass/sendall?access_token={$access_token}";
        if(isset($tag_id)){
            $filter = ['is_to_all'=>false,'tag_id'=>$tag_id];
        }else{
            $filter = ['is_to_all'=>true];
        }
        if($msgtype=='news'){
            $data = [
                'filter'=>$filter,
                'mpnews'=>[
                    'media_id'=>$media_id
                ],
                'msgtype'=>'mpnews',
                'send_ignore_reprint'=>0
            ];
        }elseif($msgtype=='image'){

            $data = [
                'filter'=>$filter,
                'image'=>[
                    'media_id'=>$media_id
                ],
                'msgtype'=>'image'
            ];

        }else{
            //只群发图文和图片  每月才4次 珍贵无比
            return false;
        }

        $data = json_encode($data,JSON_UNESCAPED_UNICODE);
        $json = $this->httpRequest($url,$data);
        $response = json_decode ( $json, true );
        return $response;


    }

    //长链接转短链接
    public function getShortUrl($access_token,$long_url){

        $url = "https://api.weixin.qq.com/cgi-bin/shorturl?access_token={$access_token}";
        $data = ['action'=>'long2short','long_url'=>$long_url];
        $data = json_encode($data);
        $json = $this->httpRequest($url,$data);
        $response = json_decode ( $json, true );
        return $response;

    }


    //发送模板信息

    public function sendTemplateMsg($access_token,$openid,$template_id,$url,$params_data,$miniprogram=false){

        $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token={$access_token}";
        if($miniprogram && is_array($miniprogram)){

            $data = [
                'touser' => $openid,
                'miniprogram'=>$miniprogram,
                'template_id' => $template_id,
                'url' => $url,
                'data' => $params_data
            ];

        }else {
            $data = [
                'touser' => $openid,
                'template_id' => $template_id,
                'url' => $url,
                'data' => $params_data
            ];
        }
        $data = json_encode($data);
        $json = $this->httpRequest($url,$data);
        $response = json_decode ( $json, true );
        return $response;


    }


     //生成临时推广码
     public function generateTempQrcode($access_token,$scene_str){

        $url = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token={$access_token}";
         $data = [
             'expire_seconds' => 86400*20,
             'action_name'=>'QR_STR_SCENE',
             'action_info' => ['scene'=>['scene_str'=>$scene_str]]
         ];
         $data = json_encode($data);
         $json = $this->httpRequest($url,$data);
         $response = json_decode ( $json, true );
         return $response;
     }
    //生成永久二维码
    public function generateQrcode($access_token,$scene_str){

        $url = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token={$access_token}";
        $data = [
            'action_name'=>'QR_LIMIT_STR_SCENE',
            'action_info' => ['scene'=>['scene_str'=>$scene_str]]
        ];
        $data = json_encode($data);
        $json = $this->httpRequest($url,$data);
        $response = json_decode ( $json, true );
        return $response;
    }


    /*
     * 获取access_token，非网页获取用户信息
     * cao，2016-04-11
     */
    public function getAccessToken() {
        //http请求方式: GET
        $path = storage_path("access_token/".$this->appId.'log');
//        file_put_contents($path,'bbbbbbb');
        $access_token = $this->get_access_toekn_file($this->appId);
        if(!$access_token){
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $this->appId . "&secret=" . $this->appSecret;
            $res = json_decode($this->httpRequest($url));
            file_put_contents($path,json_encode($res));
            if(isset($res->access_token)){
                $access_token = $res->access_token;
                $this->set_access_toekn_file($this->appId,$access_token);
            }else{
                return $res;
            }

        }
//        file_put_contents($path,json_encode($access_token));
        return $access_token;
    }
    
    private function httpsRequestGet($url) {
        $curl = curl_init ();
        curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, true );
        curl_setopt ( $curl, CURLOPT_TIMEOUT, 500 );
        // 为保证第三方服务器与微信服务器之间数据传输的安全性，所有微信接口采用https方式调用，必须使用下面2行代码打开ssl安全校验。
        // 如果在部署过程中代码在此处验证失败，请到 http://curl.haxx.se/ca/cacert.pem 下载新的证书判别文件。
        curl_setopt ( $curl, CURLOPT_SSL_VERIFYPEER, true );
        curl_setopt ( $curl, CURLOPT_SSL_VERIFYHOST, true );
        curl_setopt ( $curl, CURLOPT_URL, $url );

        $res = curl_exec ( $curl );
        curl_close ( $curl );

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

    //上传二进制文件到微信服务器  有点小坑php各版本写法均不同，自己慢慢研究 cao
    protected function uploadBinaryFile($url,$filepath){

        if(!file_exists($filepath)){
            return false;
        }
        $filetype = mime_content_type($filepath);
        $cfile = new \CURLFile($filepath,$filetype);
        $data = array('media' => $cfile);
        $ch = curl_init();
        curl_setopt($ch , CURLOPT_URL , $url);
        if (class_exists ( '/CURLFile' )) {
            curl_setopt ($ch, CURLOPT_SAFE_UPLOAD, true );
        } else {
            if (defined ( 'CURLOPT_SAFE_UPLOAD' )) {
                curl_setopt ($ch, CURLOPT_SAFE_UPLOAD, false );
            }
        }
        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, FALSE );
        curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, FALSE );
        curl_setopt ($ch, CURLOPT_SAFE_UPLOAD, true );
        curl_setopt($ch , CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch , CURLOPT_POST, 1);
        curl_setopt($ch , CURLOPT_POSTFIELDS, $data);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }


    protected function set_access_toekn_file($filename, $content)
    {
        $path = storage_path("access_token/".$filename);
        $dir =  storage_path("access_token");
        if(!is_dir($dir)){
          mkdir($dir,0777,true);
        }
        $data = json_encode(['time'=>time(),'content'=>$content]);
        $fp = file_put_contents($path,$data);
    }



    protected function get_access_toekn_file($filename)
    {
        $path = storage_path("access_token/".$filename);
        if(file_exists($path)){
            $json_data = file_get_contents($path);
            $data = json_decode($json_data,true);
            if((time()-$data['time'])>3600){
                return false;
            }
            return $data['content'];
        }
        return false;
    }











}
