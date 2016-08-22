<?php
/**
 * Email: linzongho@gmail.com
 * Github: https://github.com/linzongho/Pindex
 * User: asus
 * Date: 8/22/16
 * Time: 9:11 AM
 */
namespace {
    //---------------------------------- general constant ------------------------------//
    use Pindex\Core\Cache;
    use Pindex\Core\Dispatcher;
    use Pindex\Core\Router;
    use Pindex\Debugger;
    use Pindex\Loader;
    use Pindex\PindexException;
    const PINDEX_VERSION = 0.1;

    const TYPE_BOOL     = 'boolean';
    const TYPE_INT      = 'integer';
    const TYPE_FLOAT    = 'double';//double ,  float
    const TYPE_STR      = 'string';
    const TYPE_ARRAY    = 'array';
    const TYPE_OBJ      = 'object';
    const TYPE_RESOURCE = 'resource';
    const TYPE_NULL     = 'NULL';
    const TYPE_UNKNOWN  = 'unknown type';

    const DRIVER_DEFAULT_INDEX  = 'DRIVER_DEFAULT_INDEX';
    const DRIVER_CLASS_LIST     = 'DRIVER_CLASS_LIST';
    const DRIVER_CONFIG_LIST    = 'DRIVER_CONFIG_LIST';

    const AJAX_JSON     = 0;
    const AJAX_XML      = 1;
    const AJAX_STRING   = 2;

    const ONE_DAY   = 86400;
    const ONE_WEEK  = 604800;
    const ONE_MONTH = 2592000;

    const PINDEX_IS_CLI = PHP_SAPI === 'cli';
    define('PINDEX_IS_WIN',false !== stripos(PHP_OS, 'WIN'));//const IS_WINDOWS = PHP_OS === 'WINNT';

    if(PINDEX_IS_CLI){
        include_once __DIR__.'console.engine.php';
    }

//---------------------------------- mode constant -------------------------------------//
    defined('PINDEX_DEBUG_MODE_ON') or define('PINDEX_DEBUG_MODE_ON', true);
    defined('PINDEX_PAGE_TRACE_ON') or define('PINDEX_PAGE_TRACE_ON', true);//在处理微信签名检查时会发生以外的错误
//record status at the beginning
    PINDEX_DEBUG_MODE_ON and $GLOBALS['litex_begin'] = [
        $_SERVER['REQUEST_TIME_FLOAT'],
        memory_get_usage(),
    ];

//---------------------------------- environment constant -------------------------------------//
    //It is different to thinkphp that the beginning time is the time of request comming
    //and ThinkPHP is just using the time of calling 'microtime(true)' which ignore the loading and parsing of "ThinkPHP.php" and its include files.
    //It could always keeped in 10ms from request beginning to script shutdown.
    define('PINDEX_REQUEST_MICROTIME', $_SERVER['REQUEST_TIME_FLOAT']);//(int)($_SERVER['REQUEST_TIME_FLOAT']*1000)//isset($_SERVER['REQUEST_TIME_FLOAT'])? $_SERVER['REQUEST_TIME_FLOAT']:microtime(true)
    define('PINDEX_REQUEST_TIME',$_SERVER['REQUEST_TIME']);


    define('PINDEX_IS_AJAX', ((isset($_SERVER['HTTP_X_REQUESTED_WITH']) and strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') ));
    define('PINDEX_IS_POST',$_SERVER['REQUEST_METHOD'] === 'POST');//“GET”, “HEAD”，“POST”，“PUT”
    define('PINDEX_HTTP_PREFIX', (isset ($_SERVER ['HTTPS']) and $_SERVER ['HTTPS'] === 'on') ? 'https://' : 'http://' );
    $script_dir = rtrim(dirname($_SERVER['SCRIPT_NAME']),'/');
    define('PINDEX_PUBLIC_URL',PINDEX_HTTP_PREFIX.$_SERVER['SERVER_NAME'].
    (empty($_SERVER['SERVER_PORT']) or (80 == $_SERVER['SERVER_PORT']))?
        $script_dir : ":{$_SERVER['SERVER_PORT']}{$script_dir}");


    define('PINDEX_PATH_BASE',  PINDEX_IS_WIN?str_replace('\\','/',dirname(__DIR__)):dirname(__DIR__));
    const PINDEX_PATH_FRAMEWORK = PINDEX_PATH_BASE.'/Pindex';
    const PINDEX_PATH_CONFIG    = PINDEX_PATH_BASE.'/Config';
    const PINDEX_PATH_RUNTIME   = PINDEX_PATH_BASE.'/Runtime';
    const PINDEX_PATH_PUBLIC    = PINDEX_PATH_BASE.'/Public';

    //error  display
    error_reporting(PINDEX_DEBUG_MODE_ON?-1:E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);//php5.3version use code: error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_USER_NOTICE);
    ini_set('display_errors',PINDEX_DEBUG_MODE_ON?1:0);
    //environment
    version_compare(PHP_VERSION,'5.6','<') and die('Require php >= 5.6 !');

    if(PINDEX_DEBUG_MODE_ON) include __DIR__.'/Common/debug_suit.php';

    final class Pindex {

        /**
         * @var array 系统配置(维度为一)
         */
        public static $config = [
            'APP_NAME'          => 'Application',//决定应用目录
            'OS_ENCODING'       => 'UTF-8',//file system encoding,GB2312 for windows,and utf8 for most linux
            'EXCEPTION_CLEAN'   => false,//it will clean the output before if error or exception occur
            'TIMEZONE_ZONE'     => 'Asia/Shanghai',

            //配合nginx负载均衡达到'线路容灾'的目的
            'EXCEPTION_BACK_CODE'   => 403,
            'EXCEPTION_BACK_MESSAGE'=> 'Resource Exception!',
            'ERROR_BACK_CODE'       => 403,
            'ERROR_BACK_MESSAGE'    => 'Resource Error!',

            'ERROR_HANDLER'         => null,
            'EXCEPTION_HANDLER'     => null,

            //string
            'FUNCTION_PACK'     => null,

            //静态缓存控制
            'CACHE_URL_ON'      => false,
            'CACHE_PATH_ON'     => false,

            'ROUTE_ON'          => false,
        ];
        /**
         * @var bool 标记是否需要初始化
         */
        private static $_app_need_inited = true;

        /**
         * 初始化应用
         * @static
         * @param array $config 系统配置
         */
        public static function init(array $config=null){
            Debugger::import('app_begin',$GLOBALS['litex_begin']);
            Debugger::status('app_init_begin');
            $config and self::$config = array_merge(self::$config,$config);

//-------------------------------------------------------- general constant -----------------------------------------------------------//
            define('PINDEX_APP_NAME',self::$config['APP_NAME']);
            define('PINDEX_OS_ENCODING',self::$config['OS_ENCODING']);
            define('PINDEX_EXCEPTION_CLEAN',self::$config['EXCEPTION_CLEAN']);

            define('PINDEX_PATH_APP',   PINDEX_PATH_BASE.'/'.PINDEX_APP_NAME);
            date_default_timezone_set(self::$config['TIMEZONE_ZONE']) or die('Date default timezone set failed!');

            //behavior
            spl_autoload_register([Loader::class,'load'],false,true) or die('Faile to register class autoloader!');
            self::registerErrorHandler(self::$config['ERROR_HANDLER']);
            self::registerExceptionHandler(self::$config['EXCEPTION_HANDLER']);

            register_shutdown_function(function (){/* 脚本结束时将会自动输出，所以不能把输出控制语句放到这里 */
                PINDEX_PAGE_TRACE_ON and !PINDEX_IS_AJAX and Debugger::showTrace();//show the trace info
                Debugger::status('script_shutdown');
            });

            //function pack
            if(self::$config['FUNCTION_PACK']){
                if(is_string(self::$config['FUNCTION_PACK'])){
                    include PINDEX_PATH_BASE.self::$config['FUNCTION_PACK'];
                }elseif(is_array(self::$config['FUNCTION_PACK'])){
                    foreach (self::$config['FUNCTION_PACK'] as $item){
                        include PINDEX_PATH_BASE.$item;
                    }
                }else{
                    PindexException::throwing("Invalid config!".self::$config['FUNCTION_PACK']);
                }
            }

            self::$_app_need_inited = false;
            Debugger::status('app_init_done');
        }

        /**
         * 解析URL并调度
         * @static
         * @param array|null $config
         * @return void
         */
        public static function start(array $config=null){
            self::$_app_need_inited and self::init($config);

            //执行服务端程序
            Debugger::status('app_start');
//            $identify = md5($_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
            $identify = self::$config['CACHE_URL_ON']?str_replace('/','_',"{$_SERVER['HTTP_HOST']}-{$_SERVER['REQUEST_URI']}"):null;
            $content = $identify ? Cache::get($identify,null):null;
            //'CACHE_PATH_ON'     => true,
            if(null !== $content){
                Debugger::trace('load from url cache');
                echo $content;
            } else{
                //打开输出控制缓冲
                Cache::begin($identify);

                //parse uri
                $result = self::$config['ROUTE_ON']?Router::parseRoute():null;
                $result or $result = Router::parseURL();
                //URL中解析结果合并到$_GET中，$_GET的其他参数不能和之前的一样，否则会被解析结果覆盖,注意到$_GET和$_REQUEST并不同步，当动态添加元素到$_GET中后，$_REQUEST中不会自动添加
                empty($result['p']) or $_GET = array_merge($_GET,$result['p']);

                Debugger::status('dispatch_begin');
                //dispatch
                $ckres = Dispatcher::checkDefault($result['m'],$result['c'],$result['a']);

                $pidentify = self::$config['CACHE_PATH_ON']?str_replace('/','_',"{$ckres['m']}_{$ckres['c']}_{$ckres['a']}"):null;

                $content = $pidentify?Cache::get($pidentify,null):null;
                if(null !== $content){
                    Debugger::trace('load from path cache');
                    echo $content;
                }else{
                    //在执行方法之前定义常量,为了能在控制器的构造函数中使用这三个常量
                    define('PINDEX_REQUEST_MODULE',$ckres['m']);//请求的模块
                    define('PINDEX_REQUEST_CONTROLLER',$ckres['c']);//请求的控制器
                    define('PINDEX_REQUEST_ACTION',$ckres['a']);//请求的操作

                    $result = Dispatcher::exec();
                    //exec的结果将用于判断输出缓存，如果为int，表示缓存时间，0表示无限缓存XXX,将来将创造更多的扩展，目前仅限于int

                    if(isset($result)){
                        if (0 == $result) $result = ONE_DAY;//'无限缓存' will cause some problem
                        //it will not dispear if time not expire, remove it by hand in runtime directory!
                        if(self::$config['CACHE_URL_ON']){
                            echo Cache::end($result,$identify);
                            Debugger::trace('build url cache done!');
                        }
                        if(self::$config['CACHE_PATH_ON']){
                            echo Cache::end($result,$identify);
                            Debugger::trace('build path cache done!');
                        }
                    }else{
                        Debugger::trace('flush streightly!');
                    }
                }
            }
        }

        /**
         * 注册错误处理函数
         * @param callable|null $handler
         * @return void
         */
        private static function registerErrorHandler(callable $handler=null){
            $handler and $handler = self::$config['ERROR_HANDLER'];
            //如果之前有定义过错误处理程序，则返回该程序名称的 string；如果是内置的错误处理程序，则返回 NULL
            self::$config['ERROR_HANDLER'] = set_error_handler($handler?$handler:[PindexException::class,'handleError']);
        }

        /**
         * 注册异常处理函数
         * @param callable|null $handler
         * @return void
         */
        private static function registerExceptionHandler(callable $handler=null){
            $handler and $handler = self::$config['EXCEPTION_HANDLER'];
            //返回之前定义的异常处理程序的名称，或者在错误时返回 NULL。 如果之前没有定义一个错误处理程序，也会返回 NULL。 如果参数使用了 NULL，重置处理程序为默认状态，并且会返回一个 TRUE
            self::$config['EXCEPTION_HANDLER'] = set_exception_handler($handler?$handler:[PindexException::class,'handleException']);
        }

        /**
         * 逆初始化，取消错误异常等注册并恢复原状
         * @static
         */
        public static function unregister(){
            self::$config['ERROR_HANDLER'] and set_error_handler(self::$config['ERROR_HANDLER']);
            self::$config['EXCEPTION_HANDLER'] and set_exception_handler(self::$config['EXCEPTION_HANDLER']);
        }
    }
}

namespace Pindex {
    use Pindex\Core\Configger;
    use Pindex\Core\Response;
    use Pindex\Util\Trace;

    class Utils {

        /**
         * 数据签名认证
         * @param  mixed  $data 被认证的数据
         * @return string       签名
         * @author 麦当苗儿 <zuojiazi@vip.qq.com>
         */
        public static function dataSign($data) {
            is_array($data) or $data = [$data];
            ksort($data);
            return sha1(http_build_query($data));
        }
        /**
         * 加载显示模板
         * @param string $tpl template name in folder 'Tpl'
         * @param array|null $vars vars array to extract
         * @param bool $clean it will clean the output cache if set to true
         * @param bool $isfile 判断是否是模板文件
         */
        public static function loadTemplate($tpl,array $vars=null, $clean=true, $isfile=false){
            $clean and ob_get_level() > 0 and ob_end_clean();
            $vars and extract($vars, EXTR_OVERWRITE);
            $path = ($isfile or is_file($tpl))?$tpl:PINDEX_PATH_FRAMEWORK."/Template/{$tpl}.php";
            is_file($path) or $path = PINDEX_PATH_FRAMEWORK.'/Template/systemerror.php';
            include $path;
        }
        /**
         * 将C风格字符串转换成JAVA风格字符串
         * C风格      如： sub_string
         * JAVA风格   如： SubString
         * @param string $str
         * @param int $ori it will translate c to java style if $ori is set to true value and java to c style on false
         * @return string
         */
        public static function styleStr($str,$ori=1){
            static $cache = [];
            $key = "{$str}.{$ori}";
            if(!isset($cache[$key])){
                $cache[$key] = $ori?
                    ucfirst(preg_replace_callback('/_([a-zA-Z])/',function($match){return strtoupper($match[1]);},$str)):
                    strtolower(ltrim(preg_replace('/[A-Z]/', '_\\0', $str), '_'));
            }
            return $cache[$key];
        }

        /**
         * 自动从运行环境中获取URI
         * 直接访问：
         *  http://www.xor.com:8056/                => '/'
         *  http://localhost:8056/_xor/             => '/_xor/'  ****** BUG *******
         * @param bool $reget 是否重新获取，默认为false
         * @return null|string
         */
        public static function pathInfo($reget=false){
            static $uri = '/';
            if($reget or '/' === $uri){
                if(isset($_SERVER['PATH_INFO'])){
                    //如果设置了PATH_INFO则直接获取之
                    $uri = $_SERVER['PATH_INFO'];
                }else{
                    $scriptlen = strlen($_SERVER['SCRIPT_NAME']);
                    if(strlen($_SERVER['REQUEST_URI']) > $scriptlen){
                        $pos = strpos($_SERVER['REQUEST_URI'],$_SERVER['SCRIPT_NAME']);
                        if(false !== $pos){
                            //在不支持PATH_INFO...或者PATH_INFO不存在的情况下(URL省略将被认定为普通模式)
                            //REQUEST_URI获取原生的URL地址进行解析(返回脚本名称后面的部分)
                            if(0 === $pos){//PATHINFO模式
                                $uri = substr($_SERVER['REQUEST_URI'], $scriptlen);
                            }else{
                                //重写模式
                                $uri = $_SERVER['REQUEST_URI'];
                            }
                        }
                    }else{}//URI短于SCRIPT_NAME，则PATH_INFO等于'/'
                }
            }
            return $uri;
        }

        /**
         * 调用类的静态方法
         * 注意，调用callable的时候如果是静态方法，则不能带小括号，就像函数名称一样
         *      例如：$callable = "{$clsnm}::{$method}";将永远返回false
         * @param string $clsnm class name
         * @param string $method method name
         * @return mixed|null
         */
        public static function callStatic($clsnm,$method){
            $callable = "{$clsnm}::{$method}";
            if(is_callable($callable)){
                try{
                    return $clsnm::$method();
                }catch (\Exception $e){
                    Debugger::trace($e->getMessage());
                }
            }
            return null;
        }

        /**
         * 转换成php处理文件系统时所用的编码
         * 即UTF-8转GB2312
         * @param string $str 待转化的字符串
         * @param string $strencode 该字符串的编码格式
         * @return string|false 转化失败返回false
         */
        public static function toSystemEncode($str,$strencode='UTF-8'){
            return iconv($strencode,PINDEX_OS_ENCODING.'//IGNORE',$str);
        }

        /**
         * 转换成程序使用的编码
         * 即GB2312转UTF-8
         * @param string $str 待转换的字符串
         * @param string $program_encoding
         * @return string|false 转化失败返回false
         */
        public static function toProgramEncode($str, $program_encoding='UTF-8'){
            return iconv(PINDEX_OS_ENCODING,"{$program_encoding}//IGNORE",$str);
        }

        /**
         * 获取类常量
         * use defined() to avoid error of E_WARNING level
         * @param string $class 完整的类名称
         * @param string $constant 常量名称
         * @param mixed $replacement 不存在时的代替
         * @return mixed
         */
        public static function constant($class,$constant,$replacement=null){
            if(!class_exists($class,true)) return $replacement;
            $constant = "{$class}::{$constant}";
            return defined($constant)?constant($constant):$replacement;
        }

        /**
         * 将参数二的配置合并到参数一种，如果存在参数一数组不存在的配置项，跳过其设置
         * @param array $dest dest config
         * @param array $sourse sourse config whose will overide the $dest config
         * @param bool|false $cover it will merge the target in recursion while $cover is true
         *                  (will perfrom a high efficiency for using the built-in function)
         * @return mixed
         */
        public static function merge(array $dest,array $sourse,$cover=false){
            foreach($sourse as $key=>$val){
                $exists = key_exists($key,$dest);
                if($cover){
                    //覆盖模式
                    if($exists and is_array($dest[$key])){
                        //键存在 为数组
                        $dest[$key] = self::merge($dest[$key],$val,true);
                    }else{
                        //key not exist or not array 直接覆盖
                        $dest[$key] = $val;
                    }
                }else{
                    //非覆盖模式
                    $exists and $dest[$key] = $val;
                }
            }
            return $dest;
        }

        /**
         * 过滤掉数组中与参数二计算值相等的值，可以是保留也可以是剔除
         * @param array $array
         * @param callable|array|mixed $comparer
         * @param bool $leave
         * @return void
         */
        public static function filter(array &$array, $comparer=null, $leave=true){
            static $result = [];
            $flag = is_callable($comparer);
            $flag2 = is_array($comparer);
            foreach ($array as $key=>$val){
                if($flag?$comparer($key,$val):($flag2?in_array($val,$comparer):($comparer === $val))){
                    if($leave){
                        unset($array[$key]);
                    }else{
                        $result[$key] = $val;
                    }
                }
            }
            $leave or $array = $result;
        }

        /**
         * 从字面商判断$path是否被包含在$scope的范围内
         * @param string $path 路径
         * @param string $scope 范围
         * @return bool
         */
        public static function checkInScope($path, $scope) {
            if (false !== strpos($path, '\\')) $path = str_replace('\\', '/', $path);
            if (false !== strpos($scope, '\\')) $scope = str_replace('\\', '/', $scope);
            $path = rtrim($path, '/');
            $scope = rtrim($scope, '/');
            return (PINDEX_IS_WIN ? stripos($path, $scope) : strpos($path, $scope)) === 0;
        }

    }

    /**
     * Class Loader
     * 类加载期间
     * @package Pindex
     */
    class Loader {

        /**
         * 类名和类路径映射表
         * @var array
         */
        private static $_classes = [];

        public static function load($clsnm){
            Debugger::trace("Class '{$clsnm}' __initializationized!");
//            dump($clsnm,debug_backtrace(),class_exists($clsnm,false));
            if(isset(self::$_classes[$clsnm])) {
                include_once self::$_classes[$clsnm];
            }else{
                $pos = strpos($clsnm,'\\');
                if(false === $pos){
                    $file = PINDEX_PATH_BASE . "/{$clsnm}.class.php";//class file place deside entrance file if has none namespace
                    if(is_file($file)) include_once $file;
                }else{
                    $path = PINDEX_PATH_BASE.'/'.str_replace('\\', '/', $clsnm).'.class.php';
                    if(is_file($path)) include_once self::$_classes[$clsnm] = $path;
                }
            }
//            dump(class_exists($clsnm,false));

            //auto config class,defined by commoon
            Utils::callStatic($clsnm,'__initializationize');
        }
    }

    class PindexException extends \Exception {
        /**
         * Construct the exception. Note: The message is NOT binary safe.
         * @link http://php.net/manual/en/exception.construct.php
         * @param string $message [optional] The Exception message to throw.
         * @param int $code [optional] The Exception code.
         * @param \Exception $previous [optional] The previous exception used for the exception chaining. Since 5.3.0
         * @since 5.1.0
         */
        public function __construct($message, $code=0, \Exception $previous=null){
            $this->message = is_string($message)?$message:var_export($message,true);
        }

        /**
         * 直接抛出异常信息
         * @param ...
         * @return mixed
         * @throws PindexException
         */
        public static function throwing(){
            $clsnm = static::class;//extend class name
            throw new $clsnm(func_get_args());
        }

        /**
         * handler the exception throw by runtime-processror or user
         * @param \Exception $e ParseError(newer in php7) or Exception
         * @return void
         */
        final public static function handleException($e) {
            if(PINDEX_IS_AJAX){
                exit($e->getMessage());
            }
            PINDEX_EXCEPTION_CLEAN and ob_get_level() > 0 and ob_end_clean();
            PINDEX_DEBUG_MODE_ON or Response::sendHttpStatus(\Pindex::$config['EXCEPTION_BACK_CODE'],\Pindex::$config['EXCEPTION_BACK_MESSAGE']);
            $trace = $e->getTrace();
            if(!empty($trace[0])){
                empty($trace[0]['file']) and $trace[0]['file'] = 'Unkown file';
                empty($trace[0]['line']) and $trace[0]['line'] = 'Unkown line';

                $vars = [
                    'message'   => get_class($e).' : '.$e->getMessage(),
                    'position'  => 'File:'.$trace[0]['file'].'   Line:'.$trace[0]['line'],
                    'trace'     => $trace,
                ];
                if(PINDEX_DEBUG_MODE_ON){
                    Utils::loadTemplate('exception',$vars);
                }else{
                    Utils::loadTemplate('user_error');
                }
            }else{
                Utils::loadTemplate('user_error');
            }
            exit;
        }

        /**
         * handel the error
         * @param int $errno error number
         * @param string $errstr error message
         * @param string $errfile error occurring file
         * @param int $errline error occurring file line number
         * @return void
         */
        final public static function handleError($errno,$errstr,$errfile,$errline){
            PINDEX_EXCEPTION_CLEAN and ob_get_level() > 0 and ob_end_clean();
            if(!is_string($errstr)) $errstr = serialize($errstr);
            $trace = debug_backtrace();
            $vars = [
                'message'   => "C:{$errno}   S:{$errstr}",
                'position'  => "File:{$errfile}   Line:{$errline}",
                'trace'     => $trace, //be careful
            ];
            PINDEX_DEBUG_MODE_ON or Response::sendHttpStatus(\Pindex::$config['ERROR_BACK_CODE'],\Pindex::$config['ERROR_BACK_MESSAGE']);
            if(PINDEX_DEBUG_MODE_ON){
                Utils::loadTemplate('error',$vars);
            }else{
                Utils::loadTemplate('user_error');
            }
            exit;
        }
    }

    class Debugger {
        /**
         * @var bool
         */
        protected static $_allowTrace = true;
        /**
         * @var array 文件列表中的高亮显示的关键字
         */
        private static $keyworks = [];
        /**
         * 运行时的内存和时间状态
         * @var array
         */
        private static $_status = [];
        /**
         * 跟踪记录
         * @var array
         */
        private static $_traces = [];

        /**
         * 开启Trace
         * @return void
         */
        public static function openTrace(){
            self::$_allowTrace = true;
        }

        /**
         * 关闭trace
         * @return void
         */
        public static function closeTrace(){
            self::$_allowTrace = false;
        }

        /**
         * 记录运行时的内存和时间状态
         * @param null|string $tag tag of runtime point
         * @return void
         */
        public static function status($tag){
            PINDEX_DEBUG_MODE_ON and self::$_status[$tag] = [
                microtime(true),
                memory_get_usage(),
            ];
        }

        /**
         * import status
         * @param string $tag
         * @param array $status
         */
        public static function import($tag,array $status){
            self::$_status[$tag] = $status;
        }

        /**
         * 记录下跟踪信息
         * @param string|mixed $message
         * @param ...
         * @return string|bool
         */
        public static function trace($message){
            static $index = 0;

            if(!PINDEX_DEBUG_MODE_ON) return false;
            $location = debug_backtrace();
            if(isset($location[0])){
                $location = "{$location[0]['file']}:{$location[0]['line']}";
            }else{
                $location = $index ++;
            }
            if(func_num_args() > 1) $message = var_export(func_get_args(),true);
            if(!is_string($message)) $message = var_export($message,true);
            if(isset(self::$_traces[$location])){
                //同一个位置可能调用了多次
                $index ++;
                $location = "$location ($index)";
            }
            return self::$_traces[$location] = $message;
        }

        public static function showTrace(){
            if(self::$_allowTrace){
                return Trace::show(self::$keyworks,self::$_traces,self::$_status);
            }else{
                return false;
            }
        }

    }

    /**
     * Class AutoConfig
     * 自动类初始化
     * @package Pindex
     */
    trait AutoConfig {
        /**
         * 类的静态配置
         * @var array
         */
        private static $_cs = [];

        /**
         * initialize the class with config
         * :eg the name of this method is much special to make class initialize automaticlly
         * @param null|string $clsnm class-name
         * @return void
         */
        public static function __initializationize($clsnm=null){
            $clsnm or $clsnm = static::class;
            if(!isset(self::$_cs[$clsnm])){
                //get convention
                self::$_cs[$clsnm] = Utils::constant($clsnm,'CONF_CONVENTION',[]);

                //load the outer config
                $conf = Configger::load($clsnm);
                $conf and is_array($conf) and self::$_cs[$clsnm] = Utils::merge(self::$_cs[$clsnm],$conf,true);
            }
            //auto init
            Utils::callStatic($clsnm,'__init');
        }

        /**
         * 获取该类的配置（经过用户自定义后）
         * @param string|null $name 配置项名称
         * @param mixed $replacement 找不到对应配置时的默认配置
         * @return array
         */
        final protected static function getConfig($name=null,$replacement=null){
            $clsnm = static::class;
            isset(self::$_cs[$clsnm]) or self::$_cs[$clsnm] = [];
            return isset($name) ? (isset(self::$_cs[$clsnm][$name])?self::$_cs[$clsnm][$name]:$replacement) : (isset(self::$_cs[$clsnm])?self::$_cs[$clsnm]:$replacement);
        }

        /**
         * 设置运行时配置
         * @todo:
         * @static
         * @param string $name
         * @param mixed $value
         * @return void
         */
        final protected static function setConfig($name,$value){}

    }

    /**
     * Class AutoInstance
     * 自动类实例管理
     * @package Pindex
     */
    trait AutoInstance {
        /**
         * @var array 驱动列表
         */
        private static $_is = [];

        /**
         * 更具驱动名称和参数获取驱动实例
         * Get instance of this class of special driver by config
         * @param array|int|float|string|null $config it will convered to identify
         * @param string $clsnm class name ,it will always be driver name if value set to re-null
         * @param string|int $identify Instance identify
         * @return object
         */
        public static function getInstance($config=null,$clsnm=null,$identify=null){
            isset($clsnm) or $clsnm = static::class;
            isset($identify) or $identify = self::_getIdentify();
            if(!isset(self::$_is[$clsnm][$identify])){
                self::$_is[$clsnm][$identify] = new $clsnm($config);
            }
            return self::$_is[$clsnm][$identify];
        }

        /**
         * @param null $config
         * @return int|mixed|string
         */
        private static function _getIdentify($config=null){
            switch (gettype($config)){
                case TYPE_ARRAY:
                    $identify = Utils::dataSign($config);
                    break;
                case TYPE_FLOAT:
                case TYPE_INT:
                case TYPE_STR:
                    $identify = (string) $config;
                    break;
                case TYPE_NULL:
                    $identify = 0;
                    break;
                default:
                    return PindexException::throwing('Invalid parameter!',$config);
            }
            return $identify;
        }

        /**
         * 判断是否存在实例
         * @param array|int|float|string|null $config it will convered to identify
         * @param string $clsnm class name ,it will always be driver name if value set to re-null
         * @return bool
         */
        public static function hasInstance($config=null,$clsnm=null){
            isset($clsnm) or $clsnm = static::class;
            if(!isset(self::$_is[$clsnm])){
                self::$_is[$clsnm] = [];
                return false;
            }
            //get identify
            switch (gettype($config)){
                case TYPE_ARRAY:
                    $identify = Utils::dataSign($config);
                    break;
                case TYPE_FLOAT:
                case TYPE_INT:
                case TYPE_STR:
                    $identify = (string) $config;
                    break;
                case TYPE_NULL:
                    $identify = 0;
                    break;
                default:
                    return PindexException::throwing('不合理的参数!',$config);
            }
            return isset(self::$_is[$clsnm][$identify]);
        }
    }

    /**
     * Class Lite
     * @property array $config
     *  'sample class' => [
     *      'DRIVER_DEFAULT_INDEX' => 0,//默认驱动ID，类型限定为int或者string
     *      'DRIVER_CLASS_LIST' => [],//驱动类的列表
     *      'DRIVER_CONFIG_LIST' => [],//驱动类列表参数
     *  ]
     * @package Pindex
     */
    abstract class Lite{
        use AutoInstance,AutoConfig;

        /**
         * 类实例的驱动
         * @var object
         */
        private static $_drivers = [
            /************************************
            'sample class' => Object
             ************************************/
        ];

        /**
         * it maybe a waste of performance
         * @param string|int|null $identify it will get the default index if set to null
         * @return object
         */
        public static function driver($identify=null){
            $clsnm = static::class;
            isset(self::$_drivers[$clsnm]) or self::$_drivers[$clsnm] = [];
            $config = null;

            //get default identify
            if(null === $identify) {
                $config = static::getConfig();
                if(isset($config[DRIVER_DEFAULT_INDEX])){
                    $identify = $config[DRIVER_DEFAULT_INDEX];
                }else{
                    PindexException::throwing("找不到类'{$clsnm}'关于'{$identify}'的驱动！");
                }
            }

            //instance a driver for this identify
            if(!isset(self::$_drivers[$clsnm][$identify])){
                $config or $config = static::getConfig();
                if(isset($config[DRIVER_CLASS_LIST][$identify])){
                    self::$_drivers[$clsnm][$identify] = self::getInstance(
                        empty($config[DRIVER_CONFIG_LIST][$identify])?null:$config[DRIVER_CONFIG_LIST][$identify],//获取驱动类名称
                        $config[DRIVER_CLASS_LIST][$identify],//设置实例驱动
                        $identify //驱动标识符
                    );
                }else{
                    PindexException::throwing("找不到类'{$clsnm}'关于'{$identify}'的驱动！");
                }
            }
            return self::$_drivers[$clsnm][$identify];
        }

        /**
         * Use driver method as its static method
         * @param string $method method name
         * @param array $arguments method arguments
         * @return mixed
         */
        public static function __callStatic($method, $arguments) {
            $driver = self::driver();
            if(!method_exists($driver,$method)){
                $clsnm = static::class;
                PindexException::throwing("方法'{$method}'不存在于驱动类'{$clsnm}'中!");
            }
            return call_user_func_array([$driver, $method], $arguments);
        }
    }
}
