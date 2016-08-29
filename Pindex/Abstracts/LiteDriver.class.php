<?php
/**
 * Email: linzongho@gmail.com
 * Github: https://github.com/linzongho/Pindex
 * User: asus
 * Date: 8/27/16
 * Time: 12:45 PM
 */

namespace Pindex\Abstracts;

/**
 * Class LiteDriver
 * Lite继承类的驱动基类
 * @package Pindex\Core\Abstracts
 */
abstract class LiteDriver {

    protected $config = [];

    /**
     * LiteDriver constructor.
     * @param array|null $config 初始化参数
     */
    public function __construct(array $config=null){
        $config and $this->config = array_merge($this->config,$config);
    }

}