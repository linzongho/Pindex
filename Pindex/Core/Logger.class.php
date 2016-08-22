<?php
/**
 * Email: linzongho@gmail.com
 * Github: https://github.com/linzongho/Pindex
 * User: asus
 * Date: 8/22/16
 * Time: 11:40 AM
 */

namespace Pindex\Core;
use Pindex\Lite;
use Pindex\PindexException;

/**
 * Interface LogInterface 日志接口
 * Interface LoggerInterface
 */
interface LoggerInterface {

    /**
     * 写入日志信息
     * 如果日志文件已经存在，则追加到文件末尾
     * @param string $key 日志文件位置或者标识符（一个日志文件或者日志组是唯一的）
     * @param string|array $content 日志内容
     * @return bool 写入是否成功
     */
    public function write($key, $content);

    /**
     * 读取日志文件内容
     * 如果设置了参数二，则参数一将被认定为文件名
     * @param string $key 日志文件位置或者标识符（一个日志文件或者日志组是唯一的）
     * @return string|null 返回日志内容,指定的日志不存在时返回null
     */
    public function read($key);

}
/**
 * Class Log 日志管理类
 * @package Kbylin\System\Core
 */
class Logger extends Lite {

    /**
     * @var array 日志信息
     */
    private static $records       =  [];

    const CONF_NAME = 'log';
    const CONF_CONVENTION = [
        'DRIVER_DEFAULT_INDEX' => 0,//默认的驱动标识符，类型为int或者string
        'DRIVER_CLASS_LIST' => [
            'Pindex\\Core\\Logger\\File',
        ],//驱动类列表
        'RATE'      => Logger::LOGRATE_DAY,
        //Think\Log
        'TIME_FORMAT'   =>  ' c ',
        'FILE_SIZE'     =>  2097152,
        'PATH'  => PINDEX_PATH_RUNTIME.'/Log',
        // 允许记录的日志级别
        'LEVEL'         =>  true,//'EMERG,ALERT,CRIT,ERR,WARN,NOTIC,INFO,DEBUG,SQL',
    ];

    /**
     * 日志频率
     * LOGRATE_DAY  每天一个文件的日志频率
     * LOGRATE_HOUR 每小时一个文件的日志频率，适用于较频繁的访问
     */
    const LOGRATE_HOUR = 0;
    const LOGRATE_DAY = 1;

    /**
     * 系统预设的级别，用户也可以自定义
     */
    const LEVEL_DEBUG   = 'Debug';//错误和调试
    const LEVEL_NOTICE  = 'Notice';
    const LEVEL_INFO    = 'Info';
    const LEVEL_WARN    = 'Warn';
    const LEVEL_ERROR   = 'Error';
    const LEVEL_RECORD  = 'Record';//记录日常操作的数据信息，以便数据丢失后寻回

    /**
     * 获取日志文件的UID（Unique Identifier）
     * @param string $level 日志界别
     * @param string $datetime 日志时间标识符，如“2016-03-17/09”日期和小时之间用'/'划分
     * @return string 返回UID
     * @throws PindexException
     */
    protected static function getLogName($level=self::LEVEL_DEBUG,$datetime=null){
        if(isset($datetime)){
            $path = PINDEX_PATH_RUNTIME."/Log/{$level}/{$datetime}.log";
        }else{
            $date = date('Y-m-d');
            $rate = (self::getConfig())['RATE'];
            $rate or $rate = self::LOGRATE_DAY;
            switch($rate){
                case self::LOGRATE_DAY:
                    $path = PINDEX_PATH_RUNTIME."/Log/{$level}/{$date}.log";
                    break;
                case self::LOGRATE_HOUR:
                    $hour = date('H');
                    $path = PINDEX_PATH_RUNTIME."/Log/{$level}/{$date}/{$hour}.log";
                    break;
                default:
                    return PindexException::throwing("日志频率未定义：'{$rate}'");
            }
        }
        return $path;
    }

    /**
     * 写入日志信息
     * 如果日志文件已经存在，则追加到文件末尾
     * @param string|array $content 日志内容
     * @param string $level 日志级别
     * @return string 写入内容返回
     * @Exception FileWriteFailedException
     */
    public static function write($content,$level=self::LEVEL_DEBUG){
        is_string($content) or $content = var_export($content,true);
        return self::driver()->write(self::getLogName($level),$content);
    }

    /**
     * 读取日志文件内容
     * 如果设置了参数二，则参数一将被认定为文件名
     * @param string $datetime 日志文件生成的大致时间，记录频率为天时为yyyy-mm-dd,日志频率为时的时候为yyyy-mmmm-dd:hh
     * @param null|string $level 日志级别
     * @return string|array 如果按小时写入，则返回数组
     */
    public static function read($datetime, $level=self::LEVEL_DEBUG){
        return self::driver()->read(self::getLogName($level,$datetime));
    }

//----------------------------------------------------------------------------------------------------------//
    /**
     * 记录日志 并且会过滤未经设置的级别
     * @static
     * @access public
     * @param string $message 日志信息
     * @param string $level  日志级别
     * @param boolean $force  是否强制记录
     * @return $this
     */
    public static function record($message, $level=self::LEVEL_INFO, $force=false) {
        static $allowlevel = null;
        null === $allowlevel and $allowlevel = self::getConfig('LEVEL');
        if($force or $allowlevel or false !== strpos($allowlevel,$level)){
            self::$records[] =   "{$level}: {$message}\r\n";
        }
    }

    /**
     * 保存record记录的信息，该函数无需手动调用
     * @static
     * @access public
     * @param string $destination  写入目标
     * @return void
     */
    public static function save($destination='') {
        if(!empty(self::$records)){
            $message    =   implode('',self::$records);
            self::_write($message,$destination);
            // 保存后清空日志缓存
            self::$records = array();
        }
    }

    /**
     * 日志写入接口
     * @access public
     * @param string $log 日志信息
     * @param string $destination 写入文件
     * @return void
     */
    public static function _write($log,$destination='') {
        $config = self::getConfig();
        $now = date($config['TIME_FORMAT']);
        $destination or $destination = self::getLogName(self::LEVEL_RECORD);
        // 自动创建日志目录
        $log_dir = dirname($destination);
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0755, true);
        }
        //检测日志文件大小，超过配置大小则备份日志文件重新生成
//        if(is_file($destination) && floor($config['FILE_SIZE']) <= filesize($destination) ){
//            rename($destination,dirname($destination).'/'.time().'-'.basename($destination));
//        }
        error_log("[{$now}] ".$_SERVER['REMOTE_ADDR'].' '.$_SERVER['REQUEST_URI']."\r\n{$log}\r\n", 3,$destination);
    }
}
//一旦该类加载进来，那么这段语句必定执行，无需手动调用
register_shutdown_function(function(){
    Logger::save();
});