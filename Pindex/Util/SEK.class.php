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
                        $stripStr .= "<<<Pindex\n";
                        break;
                    case T_END_HEREDOC:
                        $stripStr .= "Pindex;\n";
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

    /**
     * 获取一个guid
     * GUID： 即Globally Unique Identifier（全球唯一标识符） 也称作 UUID(Universally Unique IDentifier) 。
     * GUID是一个通过特定算法产生的二进制长度为128位的数字标识符，用于指示产品的唯一性。GUID 主要用于在拥有多个节点
     * 、多台计算机的网络或系统中，分配必须具有唯一性的标识符。
     * 在 Windows 平台上，GUID 广泛应用于微软的产品中，用于标识如如注册表项、类及接口标识、数据库、系统目录等对象。
     * GUID 的格式为“xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx”，其中每个 x 是 0-9 或 a-f 范围内的一个32位十六进制
     * 数。例如：6F9619FF-8B86-D011-B42D-00C04FC964FF 即为有效的 GUID 值。
     * GUID在空间上和时间上具有唯一性，保证同一时间不同地方产生的数字不同。 ★世界上的任何两台计算机都不会生成重复的
     * GUID 值。★需要GUID的时候，可以完全由算法自动生成，不需要一个权威机构来管理。 ★GUID的长度固定，并且相对而言
     * 较短小，非常适合于排序、标识和存储。
     * @return string
     */
    public static function createGUID(){
        $charid = strtoupper(md5(uniqid(mt_rand(), true)));
        $hyphen = chr(45);// "-"
        $uuid = chr(123)// "{"
            .substr($charid, 0, 8).$hyphen
            .substr($charid, 8, 4).$hyphen
            .substr($charid,12, 4).$hyphen
            .substr($charid,16, 4).$hyphen
            .substr($charid,20,12)
            .chr(125);// "}"
        return $uuid;
    }

    /**
     * 判断是否是https连接
     * @return bool
     */
    public static function isHttps(){
        if (!isset($_SERVER['HTTPS'])) {
            return false;
        }
        if ($_SERVER['HTTPS'] === 1) {  //Apache
            return true;
        } elseif ($_SERVER['HTTPS'] === 'on') { //IIS
            return true;
        } elseif ($_SERVER['SERVER_PORT'] == 443) { //其他
            return true;
        }
        return false;
    }
    /**
     * 取$from~$to范围内的随机数
     *
     * @param  $from
     * @param  $to
     * @return int
     */
    public static function random($from, $to){
        $size = $from - $to; //数值区间
        $max = 30000; //最大
        if ($size < $max) {
            return $from + mt_rand(0, $size);
        } else {
            if ($size % $max) {
                return $from + self::random(0, $size / $max) * $max + mt_rand(0, $size % $max);
            } else {
                return $from + self::random(0, $size / $max) * $max + mt_rand(0, $max);
            }
        }
    }

    /**
     * 字符串截取，支持中文和其他编码
     *
     * @param string $str 需要转换的字符串
     * @param int $start 开始位置
     * @param string $length 截取长度
     * @param string $charset 编码格式
     * @param bool $suffix 截断显示字符
     * @return string
     */
    private static function msubstr($str, $start = 0, $length, $charset = "utf-8", $suffix = true){
        if (function_exists("mb_substr")) {
            $i_str_len = mb_strlen($str);
            $s_sub_str = mb_substr($str, $start, $length, $charset);
            if ($length >= $i_str_len) {
                return $s_sub_str;
            }
            return $s_sub_str . '...';
        } elseif (function_exists('iconv_substr')) {
            return iconv_substr($str, $start, $length, $charset);
        }
        $re['utf-8'] = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
        $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
        $re['gbk'] = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
        $re['big5'] = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
        preg_match_all($re[$charset], $str, $match);
        $slice = join("", array_slice($match[0], $start, $length));
        if ($suffix) return $slice . "…";
        return $slice;
    }
    /**
     * @static
     * @param int $len 长度
     * @param int $type 字串类型：0 字母 1 数字 2 大写字母 3 小写字母  4 中文,其他为数字字母混合(去掉了 容易混淆的字符oOLl和数字01，)
     * @return string
     */
    public static function randomString($len = 4, $type=5){
        $str = '';
        switch ($type) {
            case 0://大小写中英文
                $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
                break;
            case 1://数字
                $chars = str_repeat('0123456789', 3);
                break;
            case 2://大写字母
                $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;
            case 3://小写字母
                $chars = 'abcdefghijklmnopqrstuvwxyz';
                break;
            default:
                // 默认去掉了容易混淆的字符oOLl和数字01，要添加请使用addChars参数
                $chars = 'ABCDEFGHIJKMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
                break;
        }
        if ($len > 10) { // 位数过长重复字符串一定次数
            $chars = $type == 1 ? str_repeat($chars, $len) : str_repeat($chars, 5);
        }
        if ($type != 4) {
            $chars = str_shuffle($chars);
            $str = substr($chars, 0, $len);
        } else {
            // 中文随机字
            for($i = 0; $i < $len; $i ++) {
                $str .= self::msubstr($chars, floor(mt_rand(0, mb_strlen($chars, 'utf-8') - 1)), 1);
            }
        }
        return $str;
    }


    /**
     * 获取指定长度的 utf8 字符串
     * @param string $string
     * @param int $length
     * @param string $dot
     * @return string
     */
    public static function getUtf8String($string, $length, $dot = '...'){
        if (strlen($string) <= $length) return $string;

        $n = $tn = $noc = 0;

        while ($n < strlen($string)) {
            $t = ord($string[$n]);
            if ($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
                $tn = 1;
                $n++;
                $noc++;
            } elseif (194 <= $t && $t <= 223) {
                $tn = 2;
                $n += 2;
                $noc += 2;
            } elseif (224 <= $t && $t <= 239) {
                $tn = 3;
                $n += 3;
                $noc += 2;
            } elseif (240 <= $t && $t <= 247) {
                $tn = 4;
                $n += 4;
                $noc += 2;
            } elseif (248 <= $t && $t <= 251) {
                $tn = 5;
                $n += 5;
                $noc += 2;
            } elseif ($t == 252 || $t == 253) {
                $tn = 6;
                $n += 6;
                $noc += 2;
            } else {
                $n++;
            }
            if ($noc >= $length) break;
        }
        if ($noc > $length) {
            $n -= $tn;
        }
        if ($n < strlen($string)) {
            $strcut = substr($string, 0, $n);
            return $strcut . $dot;
        } else {
            return $string ;
        }
    }
    /**
     * 去掉HTML代码中的HTML标签，返回纯文本
     * @param string $document 待处理的字符串
     * @return string
     */
    public static function html2txt($document){
        $search = array ("'<script[^>]*?>.*?</script>'si", // 去掉 javascript
            "'<[\/\!]*?[^<>]*?>'si", // 去掉 HTML 标记
            "'([\r\n])[\s]+'", // 去掉空白字符
            "'&(quot|#34);'i", // 替换 HTML 实体
            "'&(amp|#38);'i",
            "'&(lt|#60);'i",
            "'&(gt|#62);'i",
            "'&(nbsp|#160);'i",
            "'&(iexcl|#161);'i",
            "'&(cent|#162);'i",
            "'&(pound|#163);'i",
            "'&(copy|#169);'i",
            "'&#(\d+);'e"); // 作为 PHP 代码运行
        $replace = array ("",
            "",
            "",
            "\"",
            "&",
            "<",
            ">",
            " ",
            chr(161),
            chr(162),
            chr(163),
            chr(169),
            "chr(\\1)");
        $text = preg_replace ($search, $replace, $document);
        return $text;
    }

    public static function utc2Timestamp($text,$replacement=0){
        if(preg_match('/Expires=([\w,\s\d-:.]*\sGMT)/',$text,$matches)){
            return isset($matches[1])?strtotime($matches[1]):$replacement;
        }else{
            return $replacement;
        }
    }

}