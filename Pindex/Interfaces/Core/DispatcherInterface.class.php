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
use Pindex\Exceptions\Dispatch\ActionAccessDenyException;


interface DispatcherInterface {

    /**
     * 获取调度的模块
     * @return string
     */
    public function getModule();

    /**
     * 获取调度的控制器
     * @return string
     */
    public function getController();

    /**
     * 获取调度的操作
     * @return string
     */
    public function getAction();

    /**
     * 检查并设置默认设置
     * @param $modules
     * @param $ctrler
     * @param $action
     * @return $this
     */
    public function check($modules,$ctrler,$action);

    /**
     * 调度到对应的action上去
     * @param string|array $modules
     * @param string|array $ctrler
     * @param string|array $action
     * @param array $params
     * @return mixed
     * @throws ActionAccessDenyException
     * @throws ControllerNotFoundException
     * @throws MethodNotExistException
     * @throws ModuleNotFoundException
     */
    public function dispatch($modules,$ctrler,$action,array $params=[]);

}