<?php

namespace App\Libraries;

//百度翻译封装
use GuzzleHttp\Client;

class Translation
{
    public static function translate($word, $from = 'en', $to = 'zh')
    {
        $app_id = config('baidu_translation.APP_ID');
        $secret = config('baidu_translation.SECRET');

        $random_num=mt_rand(100000,999999);
        $sign_params = array(
            'app_id' => $app_id,
            'secret' => $secret,
            'q' => $word,
            'random_num' => $random_num
        );

        $sign = self::makeSign($sign_params);

        $url_params = array(
            'q' => $word,
            'from' => $from,
            'to' => $to,
            'app_id' => $app_id,
            'salt' => mt_rand(100000,999999),
            'sign' => $sign
        );

        $url_string = self::toUrlParams($url_params);

        self::doGet(config('baidu_translation.HTTP_URL'), $url_string);
//        dd($url_string);



    }

    //生成签名
    protected static function makeSign($params)
    {
        ksort($params);
        $str = implode('', $params);
        return md5($str);
    }

    //拼接URL参数
    protected static function toUrlParams($params)
    {
        $url_string='';
        foreach($params as $key => $val){
            $url_string .= $key . '=' .$val . '&';
        }

        $url_string=trim($url_string,'&');
        return $url_string;
    }

    //发送请求
    protected static function doGet($url,$param_string){
        $url=$url.'?'.$param_string;
        $client = new Client();
        $res = $client->request('GET', $url);
//        echo $res->getStatusCode();
//        echo $res->getHeader('content-type');
        echo $res->getBody();
    }
}

