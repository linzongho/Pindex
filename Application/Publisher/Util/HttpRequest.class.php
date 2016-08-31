<?php

/**
 * Created by PhpStorm.
 * User: lich4ung
 * Date: 8/31/16
 * Time: 12:57 PM
 */
namespace Application\Publisher\Util;


use Pindex\Debugger;

class HttpRequest {

    /**
     * 模拟post请求
     * @param string $url
     * @param string|array $fields
     * @param string|null $cookie 是否使用cookie
     * @param bool $withHead 返回值是否带header
     * @return mixed
     */
    public static function post($url,$fields,$cookie=null,$withHead=false){
        $ch = curl_init($url);
        if(strpos($url,'https://') === 0){
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }
        if(is_array($fields) or is_object($fields)){
            $fields = http_build_query($fields);
        }
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HEADER, $withHead); //将头文件的信息作为数据流输出
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true );
        if($cookie){
            //makes curl to use the given file as source for the cookies to send to the server.
            curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
            //连接结束后，比如，调用 curl_close 后，保存 cookie 信息的文件。
            curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
        }
        $content = curl_exec($ch);
        Debugger::trace(array(
            'curl_info'   => curl_getinfo($ch),
        ));
        curl_close($ch);
        return $content;
    }

}