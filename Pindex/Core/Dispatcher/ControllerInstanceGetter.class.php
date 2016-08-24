<?php
/**
 * Github: https://github.com/linzongho/Pindex
 * Email:linzongho@gmail.com
 * User: asus
 * Date: 8/24/16
 * Time: 8:05 PM
 */

namespace Pindex\Core\Dispatcher;


interface DispatchInstanceGeneraterInterface{
    /**
     * @param string|array $module
     * @param string $controller
     * @return object|object[]
     */
    public function fetchControllerInstance($module,$controller);

    /**
     * @param string|array $module
     * @param string $controller
     * @param string $action
     * @return \ReflectionMethod|\ReflectionMethod[]
     */
    public function fetchActionInstance($module,$controller,$action);

}