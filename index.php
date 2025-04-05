<?php

function handler($request, $context) {
    // 微信公众号配置
    $token = "YuanDalao_2025_wxToken";

    // 获取请求参数
    $params = $request->getQueryParams();
    $method = $request->getMethod();

    // 处理微信服务器验证
    if (isset($params['echostr']) && $method == 'GET') {
        if (checkSignature($params, $token)) {
            return new \RingCentral\Psr7\Response(
                200,
                ['Content-Type' => 'text/plain'],
                $params['echostr']
            );
        }
    }

    // 处理微信消息
    if ($method == 'POST') {
        $postData = $request->getBody()->getContents();
        if (!empty($postData)) {
            $postObj = simplexml_load_string($postData, 'SimpleXMLElement', LIBXML_NOCDATA);
            $fromUsername = $postObj->FromUserName;
            $toUsername = $postObj->ToUserName;
            $keyword = trim($postObj->Content);
            $time = time();
            $textTpl = "<xml>
                        <ToUserName><![CDATA[%s]]></ToUserName>
                        <FromUserName><![CDATA[%s]]></FromUserName>
                        <CreateTime>%s</CreateTime>
                        <MsgType><![CDATA[text]]></MsgType>
                        <Content><![CDATA[%s]]></Content>
                        <FuncFlag>0</FuncFlag>
                        </xml>";
                        
            $contentStr = "您发送的消息是: ".$keyword;
            $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $contentStr);
            
            return new \RingCentral\Psr7\Response(
                200,
                ['Content-Type' => 'application/xml'],
                $resultStr
            );
        }
    }

    return new \RingCentral\Psr7\Response(
        200,
        ['Content-Type' => 'text/plain'],
        'success'
    );
}

function checkSignature($params, $token) {
    $signature = $params["signature"];
    $timestamp = $params["timestamp"];
    $nonce = $params["nonce"];
    
    $tmpArr = array($token, $timestamp, $nonce);
    sort($tmpArr, SORT_STRING);
    $tmpStr = implode($tmpArr);
    $tmpStr = sha1($tmpStr);
    
    return $tmpStr == $signature;
}
