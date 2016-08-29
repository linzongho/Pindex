<?php
/**
 * Email: linzongho@gmail.com
 * Github: https://github.com/linzongho/Pindex
 * User: asus
 * Date: 8/22/16
 * Time: 11:01 AM
 */
namespace Pindex\Core;
use Pindex\Exceptions\Dispatch\ActionAccessDenyException;
use Pindex\Lite;
use Pindex\PindexException;
use ReflectionMethod;

/**
 * Class Dispatcher
 * @method string getModule()
 * @method string getController()
 * @method string getAction()
 * @method $this check(string $modules,string $ctrler,string $action) 检查并设置默认设置
 * @method mixed dispatch(string|array $modules,string $ctrler,string $action,array $params=[]) 调度到对应的action上去
 * @package Pindex\Core
 */
class Dispatcher extends Lite {

    const CONF_NAME = 'dispatcher';
    const CONF_CONVENTION = [
        'DRIVER_DEFAULT_INDEX' => 0,//默认驱动ID，类型限定为int或者string
        'DRIVER_CLASS_LIST' => [
            'Pindex\\Core\\Dispatcher\\LiteDispatcher',
        ],//驱动类的列表
        'DRIVER_CONFIG_LIST'  => [
            [
                //空缺时默认补上,Done!
                'INDEX_MODULE'      => 'Home',
                'INDEX_CONTROLLER'  => 'Index',
                'INDEX_ACTION'      => 'index',
            ],
        ],
    ];

    /**
     * @var array
     */
    private static $_config = [];

    public static function __init(){
        self::$_config = self::getConfig();
    }

    /**
     * 执行控制器实例的对应方法
     * @static
     * @param object $controllerInstance
     * @param ReflectionMethod $method
     * @return mixed|null
     * @throws ActionAccessDenyException
     */
    public static function execute($controllerInstance,\ReflectionMethod $method){
        $result = null;
        if ($method->isPublic() and !$method->isStatic()) {//仅允许访问静态的公开方法
            //方法的参数检测
            if ($method->getNumberOfParameters()) {//有参数
                $args = Dispatcher::fetchMethodArgs($method);
                //执行方法
                $result = $method->invokeArgs($controllerInstance, $args);
            } else {//无参数的方法调用
                $result = $method->invoke($controllerInstance);
            }
        } else {
            throw new ActionAccessDenyException($method);
        }
        return $result;
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
            case 'POST':$vars    =  array_merge($_GET,$_POST);  break;//POST覆盖METHOD
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

}