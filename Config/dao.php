<?php
/**
 * Email: linzongho@gmail.com
 * Github: https://github.com/linzongho/Pindex
 * User: asus
 * Date: 8/23/16
 * Time: 4:40 PM
 */
return [
    'DRIVER_DEFAULT_INDEX' => 0,
    'DRIVER_CLASS_LIST' => [
        'Pindex\\Core\\Dao\\MySQL',
    ],
    'DRIVER_CONFIG_LIST' => [
        [
            'dbname'    => 'index',//选择的数据库
            'username'  => 'lin',
            'password'  => '123456',
            'host'      => 'localhost',
            'port'      => '3306',
            'charset'   => 'UTF8',
            'dsn'       => null,//默认先检查差DSN是否正确,直接写dsn而不设置其他的参数可以提高效率，也可以避免潜在的bug
            'options'   => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,//默认异常模式
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,//结果集返回形式
            ],
        ],
    ],
];