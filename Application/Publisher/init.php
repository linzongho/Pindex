<?php
/**
 * Created by PhpStorm.
 * User: lich4ung
 * Date: 9/1/16
 * Time: 1:01 PM
 */

defined('PATH_PUBLISHER') or define('PATH_PUBLISHER',dirname(__DIR__).'/');
spl_autoload_register(function($clsnm){
    static $_classes = [];
    if(isset($_classes[$clsnm])) {
        include_once $_classes[$clsnm];
    }else{
        $pos = strpos($clsnm,'\\');
        if(false === $pos){
            $file = PATH_PUBLISHER . "/{$clsnm}.php";
            if(is_file($file)) include_once $file;
        }else{
            $path = PINDEX_PATH_BASE.'/'.str_replace('\\', '/', $clsnm).'.php';
            if(is_file($path)) include_once $_classes[$clsnm] = $path;
        }
    }
});