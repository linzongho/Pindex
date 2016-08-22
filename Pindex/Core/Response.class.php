<?php
/**
 * Email: linzongho@gmail.com
 * Github: https://github.com/linzongho/Pindex
 * User: asus
 * Date: 8/22/16
 * Time: 4:13 PM
 */
namespace Pindex\Core;
use Pindex\Debugger;
use Pindex\PindexException;
use Pindex\Util\Helper\XMLHelper;

/**
 * Class Response
 * 相应类
 * @package Pindex\Core
 */
class Response {

    /**
     * 返回的消息类型
     */
    const MESSAGE_TYPE_SUCCESS = 1;
    const MESSAGE_TYPE_WARNING = -1;
    const MESSAGE_TYPE_FAILURE = 0;

    /**
     * 清空输出缓存
     * @return void
     */
    public static function cleanOutput(){
        ob_get_level() > 0 and ob_end_clean();
    }

    /**
     * flush the cache to client
     */
    public static function flushOutput(){
        ob_get_level() and ob_end_flush();
    }

    /**
     * @param bool $clean
     * @return string
     */
    public static function getOutput($clean=true){
        if(ob_get_level()){
            $content = ob_get_contents();
            $clean and ob_end_clean();
            return $content;
        }else{
            return '';
        }
    }

    /**
     * HTTP Protocol defined status codes
     * @param int $code
     * @param string $message
     */
    public static function sendHttpStatus($code,$message='') {
        static $_status = null;
        if(!$message){
            $_status or $_status = array(
                // Informational 1xx
                100 => 'Continue',
                101 => 'Switching Protocols',

                // Success 2xx
                200 => 'OK',
                201 => 'Created',
                202 => 'Accepted',
                203 => 'Non-Authoritative Information',
                204 => 'No Content',
                205 => 'Reset Content',
                206 => 'Partial Content',

                // Redirection 3xx
                300 => 'Multiple Choices',
                301 => 'Moved Permanently',
                302 => 'Found',  // 1.1
                303 => 'See Other',
                304 => 'Not Modified',
                305 => 'Use Proxy',
                // 306 is deprecated but reserved
                307 => 'Temporary Redirect',

                // Client Error 4xx
                400 => 'Bad Request',
                401 => 'Unauthorized',
                402 => 'Payment Required',
                403 => 'Forbidden',
                404 => 'Not Found',
                405 => 'Method Not Allowed',
                406 => 'Not Acceptable',
                407 => 'Proxy Authentication Required',
                408 => 'Request Timeout',
                409 => 'Conflict',
                410 => 'Gone',
                411 => 'Length Required',
                412 => 'Precondition Failed',
                413 => 'Request Entity Too Large',
                414 => 'Request-URI Too Long',
                415 => 'Unsupported Media Type',
                416 => 'Requested Range Not Satisfiable',
                417 => 'Expectation Failed',

                // Server Error 5xx
                500 => 'Internal Server Error',
                501 => 'Not Implemented',
                502 => 'Bad Gateway',
                503 => 'Service Unavailable',
                504 => 'Gateway Timeout',
                505 => 'HTTP Version Not Supported',
                509 => 'Bandwidth Limit Exceeded'
            );
            $message = isset($_status[$code])?$_status[$code]:'';
        }
        ob_get_level() > 0 and ob_end_clean();
        header("HTTP/1.1 {$code} {$message}");
    }

    /**
     * 向浏览器客户端发送不缓存命令
     * @param bool $clean clean the output before,important and default to true
     * @return void
     */
    public static function sendNocache($clean=true){
        $clean and ob_get_level() > 0 and ob_end_clean();
        header( 'Expires: Mon, 26 Jul 1997 05:00:00 GMT' );
        header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
        header( 'Cache-Control: no-store, no-cache, must-revalidate' );
        header( 'Cache-Control: post-check=0, pre-check=0', false );
        header( 'Pragma: no-cache' );
    }
    /**
     * return the request in ajax way
     * and call this method will exit the script
     * @access protected
     * @param mixed $data general type of data
     * @param int $type AJAX返回数据格式
     * @param int $options 传递给json_encode的option参数
     * @return void
     * @throws \Exception
     */
    public static function ajaxBack($data, $type = AJAX_JSON, $options = 0){
        ob_get_level() > 0 and ob_end_clean();
        Debugger::closeTrace();
        switch (strtoupper($type)) {
            case AJAX_JSON :// 返回JSON数据格式到客户端 包含状态信息
                header('Content-Type:application/json; charset=utf-8');
                exit(json_encode($data, $options));
            case AJAX_XML :// 返回xml格式数据
                header('Content-Type:text/xml; charset=utf-8');
                exit(XMLHelper::encode($data));
            case AJAX_STRING:
                header('Content-Type:text/plain; charset=utf-8');
                exit($data);
            default:
                PindexException::throwing('Invalid output type!');
        }
    }
}