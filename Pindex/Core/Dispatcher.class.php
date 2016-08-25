<?php
/**
 * Email: linzongho@gmail.com
 * Github: https://github.com/linzongho/Pindex
 * User: asus
 * Date: 8/22/16
 * Time: 11:01 AM
 */
namespace Pindex\Core;
use Pindex\Debugger;
use Pindex\Exceptions\Dispatch\ControllerNotFoundException;
use Pindex\Exceptions\Dispatch\MethodNotExistException;
use Pindex\Exceptions\Dispatch\ModuleNotFoundException;
use Pindex\Exceptions\Dispatch\ActionInvalidException;
use Pindex\Interfaces\Core\DispatcherInterface;
use Pindex\Lite;
use Pindex\PindexException;

/**
 * Class Dispatcher
 * @package Pindex\Core
 */
class Dispatcher extends Lite{

    const CONF_NAME = 'dispatcher';
    const CONF_CONVENTION = [
        //空缺时默认补上,Done!
        'INDEX_MODULE'      => 'Home',
        'INDEX_CONTROLLER'  => 'Index',
        'INDEX_ACTION'      => 'index',
        'DRIVER_DEFAULT_INDEX' => 0,//默认驱动ID，类型限定为int或者string
        'DRIVER_CLASS_LIST' => [
            'Pindex\\Core\\Dispatcher\\LiteDispatcher',
        ],//驱动类的列表
    ];

    private static $_module = null;
    private static $_controller = null;
    private static $_action = null;
    /**
     * @var array
     */
    private static $_config = [];

    /**
     * @param array|null $config
     */
    public static function init(array $config=null){
        $config and self::$_config = array_merge(self::$_config,$config);
    }

    /**
     * 匹配空缺补上默认
     * @param string|array $modules
     * @param string $ctrler
     * @param string $action
     * @return array
     */
    public static function checkDefault($modules,$ctrler,$action){
        self::$_module      = $modules?$modules:self::$_config['INDEX_MODULE'];
        self::$_controller  = $ctrler?$ctrler:self::$_config['INDEX_CONTROLLER'];
        self::$_action      = $action?$action:self::$_config['INDEX_ACTION'];

        self::$_module and is_array(self::$_module) and self::$_module = implode('/',self::$_module);

        return [
            'm' => self::$_module,
            'c' => self::$_controller,
            'a' => self::$_action,
        ];
    }

    /**
     * @var DispatcherInterface
     */
    private static $driver = null;

    /**
     * 制定对应的方法
     * @param string $modules
     * @param string $ctrler
     * @param string $action
     * @param array $parameter
     * @return mixed 方法返回什么就返回什么
     * @throws ActionInvalidException
     * @throws ControllerNotFoundException
     * @throws MethodNotExistException
     * @throws ModuleNotFoundException
     */
    public static function exec($modules=null,$ctrler=null,$action=null,array $parameter=[]){
        null === $modules   and $modules = self::$_module;
        null === $ctrler    and $ctrler = self::$_controller;
        null === $action    and $action = self::$_action;

        PINDEX_DEBUG_MODE_ON and Debugger::trace($modules,$ctrler,$action);

        self::$driver = self::driver();
        return self::$driver->dispatch($modules,$ctrler,$action,$parameter);
    }

    /**
     * 获取传递给盖饭昂奋的参数
     * @param \ReflectionMethod $targetMethod
     * @return array
     * @throws PindexException
     */
    public static function fetchMethodArgs(\ReflectionMethod $targetMethod){
        //获取输入参数
        $vars = $args = [];
        switch(strtoupper($_SERVER['REQUEST_METHOD'])){
            case 'POST':$vars    =  array_merge($_GET,$_POST);  break;
            case 'PUT':parse_str(file_get_contents('php://input'), $vars);  break;
            default:$vars  =  $_GET;
        }
        //获取方法的固定参数
        $methodParams = $targetMethod->getParameters();
        //遍历方法的参数
        foreach ($methodParams as $param) {
            $paramName = $param->getName();

            if(isset($vars[$paramName])){
                $args[] =   $vars[$paramName];
            }elseif($param->isDefaultValueAvailable()){
                $args[] =   $param->getDefaultValue();
            }else{
                return PindexException::throwing("目标缺少参数'{$param}'!");
            }
        }
        return $args;
    }

    /**
     * 加载当前访问的模块的指定配置
     * 配置目录在模块目录下的'Common/Conf'
     * @param string $name 配置名称,多个名称以'/'分隔
     * @param string $type 配置类型,默认为php
     * @return array
     */
    public static function load($name,$type=Configger::TYPE_PHP){
        if(!defined('REQUEST_MODULE')) return PindexException::throwing('\'load\'必须在\'exec\'方法之后调用!');//前提是正确制定过exec方法
        $path = PINDEX_PATH_APP.'/'.REQUEST_MODULE.'/Common/Config/';
        if(is_dir($path)){
            $file = "{$path}/{$name}.".$type;
            return Configger::load($file);
        }
        return [];
    }
}