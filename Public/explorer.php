<?php
/**
 * Email: linzongho@gmail.com
 * Github: https://github.com/linzongho/Pindex
 * User: asus
 * Date: 8/22/16
 * Time: 6:01 PM
 */
const PINDEX_DEBUG_MODE_ON = true;
const PINDEX_PAGE_TRACE_ON = true;

include '../Pindex/engine.php';
Pindex::init([
    'APP_NAME'      => 'Explorer',
]);

include '../Explorer/config/basic.php';
$app = new Application();

//语言包加载：优先级：cookie获取>自动识别
//首次没有cookie则自动识别——存入cookie,过期时间无限
if (isset($_COOKIE['kod_user_language'])) {
    $lang = $_COOKIE['kod_user_language'];
}else{//没有cookie
    preg_match('/^([a-z\-]+)/i', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $matches);
    $lang = $matches[1];
    switch (substr($lang,0,2)) {
        case 'zh':
            if ($lang != 'zn-TW'){
                $lang = 'zh-CN';
            }
            break;
        case 'en':$lang = 'en';break;
        default:$lang = 'en';break;
    }
    $lang = str_replace('-', '_',$lang);
    setcookie('kod_user_language',$lang, time()+3600*24*365);
}
if ($lang == '') $lang = 'en';

$lang = str_replace(array('/','\\','..','.'),'',$lang);
define('LANGUAGE_TYPE', $lang);
$GLOBALS['L'] = include(LANGUAGE_PATH.$lang.'/main.php');

//init setting
$setting_file = USER_SYSTEM.'system_setting.php';
if (!file_exists($setting_file)){//不存在则建立
    $setting = $GLOBALS['config']['setting_system_default'];
    $setting['menu'] = $GLOBALS['config']['setting_menu_default'];
    fileCache::save($setting_file,$setting);
}else{
    $setting = fileCache::load($setting_file);
}
if (!is_array($setting)) {
    $setting = $GLOBALS['config']['setting_system_default'];
}
if (!is_array($setting['menu'])) {
    $setting['menu'] = $GLOBALS['config']['setting_menu_default'];
}

$app->setDefaultController($setting['first_in']);//设置默认控制器
$app->setDefaultAction('index');    //设置默认控制器函数

$GLOBALS['config']['setting_system'] = $setting;//全局
$GLOBALS['L']['kod_name'] = $setting['system_name'];
$GLOBALS['L']['kod_name_desc'] = $setting['system_desc'];
if (isset($setting['powerby'])) {
    $GLOBALS['L']['kod_power_by'] = $setting['powerby'];
}

//加载用户自定义配置
$setting_user = BASIC_PATH.'config/setting_user.php';
if (file_exists($setting_user)) {
    include($setting_user);
}

$app->run();
//$app->run();
