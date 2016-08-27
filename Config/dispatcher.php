<?php
/**
 * Email: linzongho@gmail.com
 * Github: https://github.com/linzongho/Pindex
 * User: asus
 * Date: 8/27/16
 * Time: 2:18 PM
 */
return [
    'DRIVER_DEFAULT_INDEX' => 0,//默认驱动ID，类型限定为int或者string
    'DRIVER_CLASS_LIST' => [
        'Pindex\\Core\\Dispatcher\\LiteDispatcher',
        'Explorer\\DispatchHandler',
    ],//驱动类的列表
    'DRIVER_CONFIG_LIST'  => [
        [
            //空缺时默认补上,Done!
            'INDEX_MODULE'      => 'Home',
            'INDEX_CONTROLLER'  => 'Index',
            'INDEX_ACTION'      => 'index',
        ]
    ]
];