<?php

/**
 * Email: linzongho@gmail.com
 * Github: https://github.com/linzongho/Pindex
 * User: asus
 * Date: 8/22/16
 * Time: 5:15 PM
 */
namespace Pindex\Core\Logger;

use Pindex\Core\LoggerInterface;
use Pindex\Core\Storage;
use Pindex\PindexException;
use Pindex\Util\Helper\ClientAgent;

class File implements LoggerInterface{

    /**
     * 写入日志信息
     * 如果日志文件已经存在，则追加到文件末尾
     * @param string $logpath 日志文件位置或者标识符（一个日志文件或者日志组是唯一的）
     * @param string|array $content 日志内容
     * @return bool 写入是否成功
     * @throws PindexException
     */
    public function write($logpath,$content){
        $date = date('Y-m-d');
        if(is_array($content)){//数组写入
            $ready2write = var_export($content,true);
        }else{
            $ready2write = $content;
        }
        $remoteIp = ClientAgent::getClientIP();
        $ready2write = "-------------------------------------------------------------------------------------\r\n {$date[0]}  IP:{$remoteIp}  URL:{$_SERVER['REQUEST_URI']}\r\n-------------------------------------------------------------------------------------\r\n{$ready2write}\r\n\r\n\r\n\r\n";

        $dir = dirname($logpath);
        is_dir($dir) or Storage::mkdir($dir);

        if(is_file($logpath)){
            $handler = fopen($logpath,'a+');//追加方式，如果文件不存在则无法创建
            if(false === fwrite($handler,$ready2write)){
                return PindexException::throwing('Failed to write log in append mode!');
            }
            if(false === fclose($handler)) {
                return PindexException::throwing('Failed to close log file!');
            }
            return true;
        }else{
            //写入0个字节或者返回false都被认为失败
            return true == file_put_contents($logpath,$ready2write);
        }
    }


    /**
     * 读取日志文件内容
     * 如果设置了参数二，则参数一将被认定为文件名
     * @param string $logpath 日志文件位置或者标识符（一个日志文件或者日志组是唯一的）
     * @return string 返回日志内容
     */
    public function read($logpath){
        if(is_file($logpath)){
            return file_get_contents($logpath);
        }else{
            return false;
        }
    }
}