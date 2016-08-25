<?php
/**
 * Email: linzongho@gmail.com
 * Github: https://github.com/linzongho/Pindex
 * User: asus
 * Date: 8/25/16
 * Time: 10:03 AM
 */

namespace Pindex\Interfaces\Core;
use Pindex\Exceptions\Dispatch\ControllerNotFoundException;
use Pindex\Exceptions\Dispatch\MethodNotExistException;
use Pindex\Exceptions\Dispatch\ModuleNotFoundException;
use Pindex\Exceptions\Dispatch\ActionInvalidException;


interface DispatcherInterface {
    /**
     * 调度到对应的action上去
     * @param string|array $modules
     * @param string|array $ctrler
     * @param string|array $action
     * @param array $params
     * @return mixed
     * @throws ActionInvalidException
     * @throws ControllerNotFoundException
     * @throws MethodNotExistException
     * @throws ModuleNotFoundException
     */
    public function dispatch($modules,$ctrler,$action,array $params=[]);

}