<?php
/**
 * Email: linzongho@gmail.com
 * Github: https://github.com/linzongho/Pindex
 * User: asus
 * Date: 8/22/16
 * Time: 11:01 AM
 */
namespace Pindex\Core;
use Pindex\Core\Dispatcher\DispatchInstanceGeneraterInterface;
use Pindex\Debugger;
use Pindex\Exceptions\Dispatch\ActionInvalidException;
use Pindex\Exceptions\Dispatch\ControllerNotFoundException;
use Pindex\Exceptions\Dispatch\MethodNotExistException;
use Pindex\Exceptions\Dispatch\ModuleNotFoundException;
use Pindex\PindexException;

/**
 * Class Dispatcher
 * @package Pindex\Core
 */
class Dispatcher {

    private static $_module = null;
    private static $_controller = null;
    private static $_action = null;
    /**
     * @var array
     */
    private static $_config = [
        //空缺时默认补上,Done!
        'INDEX_MODULE'      => 'Home',
        'INDEX_CONTROLLER'  => 'Index',
        'INDEX_ACTION'      => 'index',
    ];

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
     * 制定对应的方法
     * @param string $modules
     * @param string $ctrler
     * @param string $action
     * @param DispatchInstanceGeneraterInterface|null $generater
     * @return mixed 方法返回什么就返回什么
     * @throws ActionInvalidException
     * @throws ControllerNotFoundException
     * @throws MethodNotExistException
     * @throws ModuleNotFoundException
     */
    public static function exec($modules=null,$ctrler=null,$action=null,DispatchInstanceGeneraterInterface $generater=null){
        null === $modules   and $modules = self::$_module;
        null === $ctrler    and $ctrler = self::$_controller;
        null === $action    and $action = self::$_action;

        PINDEX_DEBUG_MODE_ON and Debugger::trace($modules,$ctrler,$action);

        if($generater){
            $classInstance = $generater->fetchControllerInstance($modules,$ctrler);
            $method = $generater->fetchActionInstance($modules,$ctrler,$action);
        }else{
            $modulepath = PINDEX_PATH_APP."/{$modules}";//linux 不识别
            strpos($modules,'/') and $modules = str_replace('/','\\',$modules);
            //模块检测
            if(!is_dir($modulepath)){
                throw new ModuleNotFoundException($modules);
            }

            //控制器名称及存实性检测
            $className = PINDEX_APP_NAME."\\{$modules}\\Controller\\{$ctrler}";
            if(!class_exists($className,true)){
                throw new ControllerNotFoundException($className);
            }
            $classInstance =  new $className();
            //方法检测
            if(!method_exists($classInstance,$action)){
                throw new MethodNotExistException($modules,$className,$action);
            }
            $method = new \ReflectionMethod($classInstance, $action);
        }

        $result = null;
        if ($method->isPublic() and !$method->isStatic()) {//仅允许非静态的公开方法
            //方法的参数检测
            if ($method->getNumberOfParameters()) {//有参数
                $args = self::fetchMethodArgs($method);
                //执行方法
                $result = $method->invokeArgs($classInstance, $args);
            } else {//无参数的方法调用
                $result = $method->invoke($classInstance);
            }
        } else {
            throw new ActionInvalidException($method);
        }

        PINDEX_DEBUG_MODE_ON and Debugger::status('execute_end');
        return $result;
    }

    /**
     * 获取传递给盖饭昂奋的参数
     * @param \ReflectionMethod $targetMethod
     * @return array
     * @throws PindexException
     */
    private static function fetchMethodArgs(\ReflectionMethod $targetMethod){
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