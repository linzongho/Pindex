<?php
/**
 * Github: https://github.com/linzongho/Pindex
 * Email:linzongho@gmail.com
 * User: asus
 * Date: 8/24/16
 * Time: 10:17 PM
 */

namespace Explorer;
use Pindex\Core\Dispatcher;
use Pindex\Exceptions\Dispatch\ActionAccessDenyException;
use Pindex\Exceptions\Dispatch\ControllerNotFoundException;
use Pindex\Exceptions\Dispatch\MethodNotExistException;
use Pindex\Exceptions\Dispatch\ModuleNotFoundException;
use Pindex\Interfaces\Core\DispatcherInterface;

class DispatchHandler implements DispatcherInterface {
    /**
     * @var object[]
     */
    private $controllers = [];
    /**
     * @var \ReflectionMethod[]
     */
    private $methods = [];

    /**
     * 获取空
     * @param $controller
     * @return object
     */
    private function getControllerInstance($controller){
        if(!isset($this->controllers[$controller])){
            include_once PINDEX_PATH_BASE.'/Explorer/Controller/'.$controller.'.class.php';
            $this->controllers[$controller] = new $controller();
        }
        return $this->controllers[$controller];
    }

    /**
     * @param string $controller
     * @param string $action
     * @return array
     * @throws MethodNotExistException
     */
    private function getMethodInstance($controller,$action){
        $key = $controller.'+'.$action;
        if(!isset($this->methods[$key])){
            $controllerInstance = $this->getControllerInstance($controller);
            //方法检测
            if(!method_exists($controllerInstance,$action)) throw new MethodNotExistException($controllerInstance,$action);
            $this->methods[$key] = new \ReflectionMethod($controllerInstance, $action);
        }
        return $this->methods[$key];
    }


    private $controller = null;
    private $action = null;

    /**
     * 获取调度的模块
     * @return string
     */
    public function getModule()
    {
        return '';
    }

    /**
     * 获取调度的控制器
     * @return string
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * 获取调度的操作
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * 检查并设置默认设置
     * @param $modules
     * @param $ctrler
     * @param $action
     * @return $this
     */
    public function check($modules, $ctrler, $action)
    {
        $this->controller = $ctrler;
        $this->action = $action;
        return $this;
    }

    /**
     * 调度到对应的action上去,
     * @param string|array $modules
     * @param string|array $ctrlers
     * @param string|array $actions
     * @param array $params
     * @return mixed
     * @throws ActionAccessDenyException
     * @throws ControllerNotFoundException
     * @throws MethodNotExistException
     * @throws ModuleNotFoundException
     */
    public function dispatch($modules, $ctrlers, $actions, array $params = []){

        if(!is_array($ctrlers)) $ctrlers = [$ctrlers];
        if(!is_array($actions)) $actions = [$actions];
        $maxlen = count($ctrlers);
        $maxlen2 = count($actions);

        $maxlen2 > $maxlen and $maxlen = $maxlen2;

        $result = null;
        for($i = 0; $i < $maxlen; $i++){
            $controller = isset($ctrlers[$i])?$ctrlers[$i]:$ctrlers[0];
            $action = isset($actions[$i])?$actions[$i]:$actions[0];

            $controllerInstance = $this->getControllerInstance($controller);
            $methodInstance = $this->getMethodInstance($controller,$action);
            $result = Dispatcher::execute($controllerInstance,$methodInstance);
        }
        return $result;//只有最后一个结果才能被返回
    }
}