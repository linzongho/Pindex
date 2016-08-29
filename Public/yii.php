<?php
/**
 * Github: https://github.com/linzongho/Pindex
 * Email:linzongho@gmail.com
 * User: asus
 * Date: 8/25/16
 * Time: 7:39 PM
 */

// change the following paths if necessary
$yii=__DIR__.'/../Vendor/yiiframework/yii.php';
$config=__DIR__.'/../Yii/protected/config/main.php';
include '../Pindex/Common/debug_suit.inc.php';

// remove the following lines when in production mode
defined('YII_DEBUG') or define('YII_DEBUG',true);
// specify how many levels of call stack should be shown in each log message
defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL',3);

require_once($yii);
Yii::createWebApplication($config)->run();
