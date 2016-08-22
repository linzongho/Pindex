<?php
/**
 * Email: linzongho@gmail.com
 * Github: https://github.com/linzongho/Pindex
 * User: asus
 * Date: 8/22/16
 * Time: 9:36 AM
 */
namespace Pindex;
function _buildMessage4Http($params,$traces){
    $color='#';$str='9ABCDEF';//随机浅色背景
    for($i=0;$i<6;$i++) $color=$color.$str[rand(0,strlen($str)-1)];
    $str = "<pre style='background: {$color};width: 100%;padding: 10px;margin: 0'><h3 style='color: midnightblue'><b>F:</b>{$traces[0]['file']} << <b>L:</b>{$traces[0]['line']} >> </h3>";
    foreach ($params as $key=>$val) $str .= '<b>Parameter-'.$key.':</b><br />'.var_export($val, true).'<br />';
    return $str.'</pre>';
}
function _buildMessage4Client($params,$traces){
    $str = "F:{$traces[0]['file']} << L:{$traces[0]['line']} >>".PHP_EOL;
    foreach ($params as $key=>$val) $str .= "[Parameter-{$key}]\n".var_export($val, true).PHP_EOL;
    return $str;
}

/**
 * @param ... it will return all message debugged if sum of parameters is zero
 * @return string|array
 */
function debug(){
    static $_messages = [];
    if(func_num_args()){

        return $_messages[] = call_user_func_array(PINDEX_IS_CLI?'\Pindex\_buildMessage4Client':'\Pindex\_buildMessage4Http',[func_get_args(),debug_backtrace()]);
    }else{
        return $_messages;
    }
}
/**
 * @param ...
 */
function dump(){
    echo call_user_func_array(PINDEX_IS_CLI?'\Pindex\_buildMessage4Client':'\Pindex\_buildMessage4Http',[func_get_args(),debug_backtrace()]);
}

/**
 * @param ...
 * @return void
 */
function dumpout(){
    echo call_user_func_array(PINDEX_IS_CLI?'\Pindex\_buildMessage4Client':'\Pindex\_buildMessage4Http',[func_get_args(),debug_backtrace()]);
    exit();
}
