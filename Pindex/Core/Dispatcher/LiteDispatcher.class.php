<?php
/**
 * Email: linzongho@gmail.com
 * Github: https://github.com/linzongho/Pindex
 * User: asus
 * Date: 8/25/16
 * Time: 10:01 AM
 */

namespace Pindex\Core\Dispatcher;
use Pindex\Core\Dispatcher;
use Pindex\Debugger;
use Pindex\Exceptions\Dispatch\ControllerNotFoundException;
use Pindex\Exceptions\Dispatch\MethodNotExistException;
use Pindex\Exceptions\Dispatch\ModuleNotFoundException;
use Pindex\Exceptions\Dispatch\ActionInvalidException;
use Pindex\Interfaces\Core\DispatcherInterface;

class LiteDispatcher implements DispatcherInterface{

    /**
     * @param array|string $modules
     * @param array|string $ctrler
     * @param array|string $action
     * @param array $params
     * @return mixed|null
     * @throws ActionInvalidException
     * @throws ControllerNotFoundException
     * @throws MethodNotExistException
     * @throws ModuleNotFoundException
     */
    public function dispatch($modules,$ctrler,$action,array $params=[]){
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

        $result = null;
        if ($method->isPublic() and !$method->isStatic()) {//仅允许非静态的公开方法
            //方法的参数检测
            if ($method->getNumberOfParameters()) {//有参数
                $args = Dispatcher::fetchMethodArgs($method);
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

}