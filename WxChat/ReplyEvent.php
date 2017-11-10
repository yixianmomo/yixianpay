<?php

namespace Yixianmomo\WxChat;


class ReplyEvent
{


//回复文本信息
    public function responseText($object, $content)
    {
        $textTpl = "<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
                    <MsgType><![CDATA[text]]></MsgType>
                    <Content><![CDATA[%s]]></Content>
                    <FuncFlag>0</FuncFlag>
                    </xml>";
        $resultStr = sprintf($textTpl, $object->FromUserName, $object->ToUserName, time(), $content);
        return $resultStr;
    }


//响应单条图文信息
    public function responseSingleNews($object, $title, $description, $picurl, $url)
    {
        $imgtpl = "<xml>
                <ToUserName><![CDATA[%s]]></ToUserName>
                <FromUserName><![CDATA[%s]]></FromUserName>
                <CreateTime>%s</CreateTime>
                <MsgType><![CDATA[news]]></MsgType>
                <ArticleCount>1</ArticleCount>
                <Articles>
                <item>
                <Title><![CDATA[%s]]></Title>
                <Description><![CDATA[%s]]></Description>
                <PicUrl><![CDATA[%s]]></PicUrl>
                <Url><![CDATA[%s]]></Url>
                </item>
                </Articles>
                </xml>";
        $resultStr = sprintf($imgtpl, $object->FromUserName, $object->ToUserName, time(), $title, $description, $picurl, $url);
        return $resultStr;
    }


    // 响应多条多条信息
    public function responseMultNews($object, $arr_item, $funcFlag = 0)
    {
        // 首条标题28字，其他标题39字
        if (!is_array($arr_item))
            return;

        $itemTpl = "<item>
				        <Title><![CDATA[%s]]></Title>
				        <Description><![CDATA[%s]]></Description>
				        <PicUrl><![CDATA[%s]]></PicUrl>
				        <Url><![CDATA[%s]]></Url>
				    </item>
				";
        $item_str = "";
        foreach ($arr_item as $item) {
            $item_str .= sprintf($itemTpl, $item ['Title'], $item ['Description'], $item ['PicUrl'], $item ['Url']);
        }
        $newsTpl = "<xml>
				<ToUserName><![CDATA[%s]]></ToUserName>
				<FromUserName><![CDATA[%s]]></FromUserName>
				<CreateTime>%s</CreateTime>
				<MsgType><![CDATA[news]]></MsgType>
				<ArticleCount>%s</ArticleCount>
				<Articles>{$item_str}</Articles>
				</xml>";

        $resultStr = sprintf($newsTpl, $object->FromUserName, $object->ToUserName, time(), count($arr_item));
        return $resultStr;
    }

    //回复单张图片
    public function responseImage($object,$media_id)
    {
        $textTpl = "<xml>
        <ToUserName><![CDATA[%s]]></ToUserName>
        <FromUserName><![CDATA[%s]]></FromUserName>
        <CreateTime>%s</CreateTime>
        <MsgType><![CDATA[image]]></MsgType>
        <Image>
        <MediaId><![CDATA[%s]]></MediaId>
        </Image>
        </xml>";
        $resultStr = sprintf($textTpl, $object->FromUserName, $object->ToUserName, time(), $media_id);
        return $resultStr;
    }












//事件推送
    private function PushEvent($object)
    {
        $contentStr = "";
        switch ($object->Event) {
            case "subscribe":
                $this->EventBySubscrib();
                break;
            case "SCAN":
                $this->EventByScan();
                break;
            case "LOCATION":
                $contentStr = $this->EventByLocation();
                break;
            case "CLICK":
                $this->EventByClick();
                break;
            case "VIEW":
                $this->EventByView();
                break;
            default :
                $contentStr = "Unknow Event: " . $object->Event;
                break;
        }
        $resultStr = $this->responseText($object, $contentStr);
        return $resultStr;
    }

    private function EventByLocation()
    {
        $text = "您好，你分享的位置已收到，系统已记录你的坐标。谢谢！！！\n";
        return $text;
    }


}
