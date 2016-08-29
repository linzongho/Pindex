<?php
/**
 * Email: linzongho@gmail.com
 * Github: https://github.com/linzongho/Pindex
 * User: asus
 * Date: 8/27/16
 * Time: 2:17 PM
 */
return [
    'DRIVER_DEFAULT_INDEX' => 0,//默认驱动ID，类型限定为int或者string
    'DRIVER_CLASS_LIST' => [
        'Pindex\\Core\\Router\\LiteRouter',
        'Explorer\\ExplorerRouter',
    ],//驱动类的列表
    'DRIVER_CONFIG_LIST' => [
        [
            //@see LiteRouter's config
        ]
    ],//驱动类列表参数
];