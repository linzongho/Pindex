<?php
/**
 * Email: linzongho@gmail.com
 * Github: https://github.com/linzongho/Pindex
 * User: asus
 * Date: 8/22/16
 * Time: 9:43 AM
 */

namespace Pindex\Util;


class SEK {

    /**
     * 调用位置
     */
    const PLACE_BACKWORD           = 0; //表示调用者自身的位置
    const PLACE_SELF               = 1;// 表示调用调用者的位置
    const PLACE_FORWARD            = 2;
    const PLACE_FURTHER_FORWARD    = 3;
    /**
     * 信息组成
     */
    const ELEMENT_FUNCTION = 1;
    const ELEMENT_FILE     = 2;
    const ELEMENT_LINE     = 4;
    const ELEMENT_CLASS    = 8;
    const ELEMENT_TYPE     = 16;
    const ELEMENT_ARGS     = 32;
    const ELEMENT_ALL      = 0;

    /**
     * 获取调用者本身的位置
     * @param int $elements 为0是表示获取全部信息
     * @param int $place 位置属性
     * @return array|string
     */
    public static function backtrace($elements=self::ELEMENT_ALL, $place=self::PLACE_SELF) {
        $trace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT);
        $result = [];
        if($elements){
            $elements & self::ELEMENT_ARGS     and $result[self::ELEMENT_ARGS]    = isset($trace[$place]['args'])?$trace[$place]['args']:null;
            $elements & self::ELEMENT_CLASS    and $result[self::ELEMENT_CLASS]   = isset($trace[$place]['class'])?$trace[$place]['class']:null;
            $elements & self::ELEMENT_FILE     and $result[self::ELEMENT_FILE]    = isset($trace[$place]['file'])?$trace[$place]['file']:null;
            $elements & self::ELEMENT_FUNCTION and $result[self::ELEMENT_FUNCTION]= isset($trace[$place]['function'])?$trace[$place]['function']:null;
            $elements & self::ELEMENT_LINE     and $result[self::ELEMENT_LINE]    = isset($trace[$place]['line'])?$trace[$place]['line']:null;
            $elements & self::ELEMENT_TYPE     and $result[self::ELEMENT_TYPE]    = isset($trace[$place]['type'])?$trace[$place]['type']:null;
            1 === count($result) and $result = array_shift($result);//一个结果直接返回
        }else{
            $result = $trace[$place];
        }
        return $result;
    }

    /**
     * 解析模板位置
     * 测试代码：
    [
    SEK::parseLocation('ModuleA/ModuleB@ControllerName/ActionName:themeName'),
    SEK::parseLocation('ModuleA/ModuleB@ControllerName/ActionName'),
    SEK::parseLocation('ControllerName/ActionName:themeName'),
    SEK::parseLocation('ControllerName/ActionName'),
    SEK::parseLocation('ActionName'),
    SEK::parseLocation('ActionName:themeName'),
    ]
     * @param string $location 模板位置
     * @return array
     */
    public static function parseLocation($location){
        //资源解析结果：元素一表示解析结果
        $result = [
            't' => null,
            'm' => null,
            'c' => null,
            'a' => null,
        ];

        //-- 解析字符串成数组 --//
        $tpos = strpos($location,':');
        //解析主题
        if(false !== $tpos){
            //存在主题
            $result['t'] = substr($location,$tpos+1);//末尾的pos需要-1-1
            $location = substr($location,0,$tpos);
        }
        //解析模块
        $mcpos = strpos($location,'@');
        if(false !== $mcpos){
            $result['m'] = substr($location,0,$mcpos);
            $location = substr($location,$mcpos+1);
        }
        //解析控制器和方法
        $capos = strpos($location,'/');
        if(false !== $capos){
            $result['c'] = substr($location,0,$capos);
            $result['a'] = substr($location,$capos+1);
        }else{
            $result['a'] = $location;
        }

        return $result;
    }

    /**
     * 去除代码中的空白和注释
     * @param string $content 代码内容
     * @return string
     */
    public static function stripWhiteSpace($content) {
        $stripStr   = '';
        //分析php源码
        $tokens     = token_get_all($content);
        $last_space = false;
        for ($i = 0, $len = count($tokens); $i < $len; $i++) {
            if (is_string($tokens[$i])) {
                $last_space = false;
                $stripStr  .= $tokens[$i];
            } else {
                switch ($tokens[$i][0]) {
                    //过滤各种php注释
                    case T_COMMENT:
                    case T_DOC_COMMENT:
                        break;
                    //过滤空格
                    case T_WHITESPACE:
                        if (!$last_space) {
                            $stripStr  .= ' ';
                            $last_space = true;
                        }
                        break;
                    case T_START_HEREDOC:
                        $stripStr .= "<<<PLite\n";
                        break;
                    case T_END_HEREDOC:
                        $stripStr .= "PLite;\n";
                        for($k = $i+1; $k < $len; $k++) {
                            if(is_string($tokens[$k]) && $tokens[$k] == ';') {
                                $i = $k;
                                break;
                            } else if($tokens[$k][0] == T_CLOSE_TAG) {
                                break;
                            }
                        }
                        break;
                    default:
                        $last_space = false;
                        $stripStr  .= $tokens[$i][1];
                }
            }
        }
        return $stripStr;
    }
    /**
     * 数组递归遍历
     * @param array $array 待递归调用的数组
     * @param callable $filter 遍历毁掉函数
     * @param bool $keyalso 是否也应用到key上
     * @return array
     */
    public static function arrayRecursiveWalk(array $array, callable $filter,$keyalso=false) {
        static $recursive_counter = 0;
        if (++ $recursive_counter > 1000) die( 'possible deep recursion attack' );
        $result = [];
        foreach ($array as $key => $val) {
            $result[$key] = is_array($val) ? self::arrayRecursiveWalk($val,$filter,$keyalso) : call_user_func($filter, $val);

            if ($keyalso and is_string ( $key )) {
                $new_key = $filter ( $key );
                if ($new_key != $key) {
                    $array [$new_key] = $array [$key];
                    unset ( $array [$key] );
                }
            }
        }
        -- $recursive_counter;
        return $result;
    }
}