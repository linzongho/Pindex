<?php
/**
 * Email: linzongho@gmail.com
 * Github: https://github.com/linzongho/Pindex
 * User: asus
 * Date: 8/22/16
 * Time: 9:36 AM
 */
namespace Pindex;
function _buildMessage4Http($params, $traces)
{
    $color = '#';
    $str = '9ABCDEF';//随机浅色背景
    for ($i = 0; $i < 6; $i++) $color = $color . $str[rand(0, strlen($str) - 1)];
    $str = "<pre style='background: {$color};width: 100%;padding: 10px;margin: 0'><h3 style='color: midnightblue'><b>F:</b>{$traces[0]['file']} << <b>L:</b>{$traces[0]['line']} >> </h3>";
    foreach ($params as $key => $val) $str .= '<b>Parameter-' . $key . ':</b><br />' . var_export($val, true) . '<br />';
    return $str . '</pre>';
}

function _buildMessage4Client($params, $traces)
{
    $str = "F:{$traces[0]['file']} << L:{$traces[0]['line']} >>" . PHP_EOL;
    foreach ($params as $key => $val) $str .= "[Parameter-{$key}]\n" . var_export($val, true) . PHP_EOL;
    return $str;
}

/**
 * @param ... it will return all message debugged if sum of parameters is zero
 * @return string|array
 */
function debug()
{
    static $_messages = [];
    if (func_num_args()) {

        return $_messages[] = call_user_func_array(PINDEX_IS_CLI ? '\Pindex\_buildMessage4Client' : '\Pindex\_buildMessage4Http', [func_get_args(), debug_backtrace()]);
    } else {
        return $_messages;
    }
}

/**
 * @param ...
 */
function dump()
{
    echo _buildMessage(func_get_args(), debug_backtrace());
}

/**
 * @param ...
 * @return void
 */
function dumpout()
{
    echo _buildMessage(func_get_args(), debug_backtrace());
    exit();
}

/**
 * 格式化输出变量，或者对象
 *
 * @param array $params
 * @param array $traces
 * @return string
 */
function _buildMessage($params, $traces)
{
    static $styleout = false;
    ob_start();
    $style = '<style>
	pre#debug{margin:10px;font-size:14px;color:#222;font-family:Consolas ;line-height:1.2em;background:#f6f6f6;border-left:5px solid #444;padding:5px;width:95%;word-break:break-all;}
	pre#debug b{font-weight:400;}
	#debug #debug_str{color:#E75B22;}
	#debug .debug_keywords{font-weight:800;color:#00f;}
	#debug .debug_tag1{color:#22f;}
	#debug .debug_tag2{color:#f33;font-weight:800;}
	#debug .debug_var{color:#33f;}
	#debug .debug_var_str{color:#f00;}
	#debug .debug_set{color:#0C9CAE;}</style>';

    echo "<h3 style='color: midnightblue'><b>File:</b> {$traces[0]['file']} << <b>Line:</b> {$traces[0]['line']} >> </h3 >";
    foreach ($params as $key => $param) {
        echo "<b class='debug_keywords'>Parameter:{$key}</b><br />";

        if (is_array($param)) {
            print_r($param);
        } else if (is_object($param)) {
            echo get_class($param) . " Object";
        } else if (is_resource($param)) {
            echo (string)$param;
        } else {
            echo var_dump($param);
        }
    }

    $out = ob_get_clean(); //缓冲输出给$out 变量
    $out = preg_replace([
//        '/"(.*)"/', //高亮字符串变量
//        '/=\>(.*)/',//高亮=>后面的值
//        '/\[(.*)\]/', //高亮变量
    ], [
//        '<b class="debug_var_str">"' . '\\1' . '"</b>',
//        '<b class="debug_str">' . '\\1' . '</b>',
//        '<b class="debug_tag1">[</b><b class="debug_var">' . '\\1' . '</b><b class="debug_tag1">]</b>',

    ], $out);
//    $out = str_replace([
//        '    ',
//        '(', ')',
//        '=>',
//    ], [
//        '  ',
//        '<b class="debug_tag2">(</i>', '<b class="debug_tag2">)</b>',
//        '<b class="debug_set">=></b>',
//    ], $out);

    $keywords = array('Array', 'int', 'string', 'class', 'object', 'null'); //关键字高亮
    $keywords_to = $keywords;
    foreach ($keywords as $key => $val) {
        $keywords_to[$key] = ' class="debug_keywords">' . $val . '</b>';
    }
    $out = str_replace($keywords, $keywords_to, $out);
    $out = str_replace("\n\n", "\n", $out);
    if (!$styleout) {
        echo $style;
        $styleout = true;
    }
    echo '<pre id="debug">' . $out . ' </pre > ';
    return ob_get_clean();
}


/**
 * 获取变量的名字
 * eg hello="123" 获取ss字符串
 * @param $aVar
 * @return int|string
 */
function _get_var_name(&$aVar){
    foreach($GLOBALS as $key => $var) {
        if ($aVar == $GLOBALS[$key] && $key != "argc") {
            return $key;
        }
    }
    return 'Unknown';
}

/**
 * 格式化输出变量，或者对象
 *
 * @param mixed $var
 * @param boolean $exit
 */
function println($var, $exit = false){
    ob_start();
    $style = '<style>
	pre#debug{margin:10px;font-size:14px;color:#222;font-family:Consolas ;line-height:1.2em;background:#f6f6f6;border-left:5px solid #444;padding:5px;width:95%;word-break:break-all;}
	pre#debug b{font-weight:400;}
	#debug #debug_str{color:#E75B22;}
	#debug #debug_keywords{font-weight:800;color:#00f;}
	#debug #debug_tag1{color:#22f;}
	#debug #debug_tag2{color:#f33;font-weight:800;}
	#debug #debug_var{color:#33f;}
	#debug #debug_var_str{color:#f00;}
	#debug #debug_set{color:#0C9CAE;}</style>';
    if (is_array($var)) {
        print_r($var);
    } else if (is_object($var)) {
        echo get_class($var) . " Object";
    } else if (is_resource($var)) {
        echo (string)$var;
    } else {
        echo var_dump($var);
    }
    $out = ob_get_clean(); //缓冲输出给$out 变量
    $out = preg_replace('/"(.*)"/', '<b id="debug_var_str">"' . '\\1' . '"</b>', $out); //高亮字符串变量
    $out = preg_replace('/=\>(.*)/', '=>' . '<b id="debug_str">' . '\\1' . '</b>', $out); //高亮=>后面的值
    $out = preg_replace('/\[(.*)\]/', '<b id="debug_tag1">[</b><b id="debug_var">' . '\\1' . '</b><b id="debug_tag1">]</b>', $out); //高亮变量
    $from = array('    ', '(', ')', '=>');
    $to = array('  ', '<b id="debug_tag2">(</i>', '<b id="debug_tag2">)</b>', '<b id="debug_set">=></b>');
    $out = str_replace($from, $to, $out);

    $keywords = array('Array', 'int', 'string', 'class', 'object', 'null'); //关键字高亮
    $keywords_to = $keywords;
    foreach($keywords as $key => $val) {
        $keywords_to[$key] = '<b id="debug_keywords">' . $val . '</b>';
    }
    $out = str_replace($keywords, $keywords_to, $out);
    $out = str_replace("\n\n", "\n", $out);
    $traces = debug_backtrace();

    echo $style . '<pre id="debug">'.
        "<h3 style='color: midnightblue'><b>File:</b> {$traces[0]['file']} << <b>Line:</b> {$traces[0]['line']} >> </h3 > <br />".
        '<b id="debug_keywords">' . _get_var_name($var) . '</b> = ' . $out . '</pre>';
    if ($exit) exit; //为真则退出
}

function printlns(){
    $avg_num = func_num_args();
    $avg_list = func_get_args();
    ob_start();
    for($i = 0; $i < $avg_num; $i++) {
        println($avg_list[$i],false);
    }
    $out = ob_get_clean();
    echo $out;
    exit;
}

