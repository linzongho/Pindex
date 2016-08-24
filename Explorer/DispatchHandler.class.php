<?php
/**
 * Github: https://github.com/linzongho/Pindex
 * Email:linzongho@gmail.com
 * User: asus
 * Date: 8/24/16
 * Time: 10:17 PM
 */

namespace Explorer;
use Pindex\Core\Dispatcher\DispatchInstanceGeneraterInterface;
use Pindex\Exceptions\Dispatch\MethodNotExistException;

class DispatchHandler implements DispatchInstanceGeneraterInterface {
    /**
     * @var object[]
     */
    private $instances = [];

    /**
     * @param string|array $module
     * @param string $controller
     * @return object|object[]
     */
    public function fetchControllerInstance($module='', $controller){
        $instances = [];
        if(is_array($controller)){
            $len = count($controller);
            for($i = 0 ; $i < $len; $i ++){
                $ctler = $controller[$i];
                include_once PINDEX_PATH_BASE.'/Explorer/Controller'.$controller.'.class.php';
                $instances[] = $this->instances[$module.$controller] = new $controller();
            }
        }
        return $instances;
    }

    /**
     * @param string|array $module
     * @param string $controller
     * @param string $action
     * @return \ReflectionMethod|\ReflectionMethod[]
     * @throws MethodNotExistException
     */
    public function fetchActionInstance($module='', $controller, $action){
        $actions = [];
        if($controller){

        }
        if(!isset($this->instances[$module.$controller])){
            include_once PINDEX_PATH_BASE.'/Explorer/Controller'.$controller.'.class.php';
            $this->instances[$module.$controller] = new $controller();
        }
        $classInstance = $this->instances[$module.$controller];
        //方法检测
        if(!method_exists($classInstance,$action)){
            throw new MethodNotExistException($controller,$action);
        }
        $actions[] = new \ReflectionMethod($classInstance, $action);
        return $actions;
    }
}