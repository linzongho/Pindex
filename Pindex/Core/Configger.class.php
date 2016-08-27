<?php
/**
 * Email: linzongho@gmail.com
 * Github: https://github.com/linzongho/Pindex
 * User: asus
 * Date: 8/22/16
 * Time: 10:40 AM
 */
namespace Pindex\Core;
use Pindex\Debugger;
use Pindex\PindexException;
use Pindex\Utils;

/**
 * Class Configger 配置管理类
 * @package Pindex\Core
 */
class Configger {
    /**
     * 配置类型
     * 值使用字符串而不是效率更高的数字是处于可以直接匹配后缀名的考虑
     */
    const TYPE_PHP     = 'php';
    const TYPE_INI     = 'ini';
    const TYPE_YAML    = 'yaml';
    const TYPE_XML     = 'xml';
    const TYPE_JSON    = 'json';

    /**
     * @var string config file-build path
     */
    private static $configs_path = PINDEX_PATH_RUNTIME.'/configs.php';
    /**
     * @var array map of class fullname of its config name
     */
    private static $_map = [];

    /**
     * @var array config of this class
     */
    private static $_config = [
        'AUTO_BUILD'        => true,
        'AUTO_CLASS_LIST'   => [
            'Pindex\\Core\\Dao',
            'Pindex\\Core\\Cache',
            'Pindex\\Core\\Router',
            'Pindex\\Library\\View',
        ],
        'USER_CONFIG_PATH'  => PINDEX_PATH_RUNTIME.'/dynamic_config/',
    ];
    /**
     * @var array
     */
    private static $_cache = null;

    /**
     * Init the config cache
     * @param array $config
     * @return void
     */
    public static function __init(array $config=null){
        $config and self::$_config = array_merge(self::$_config,$config);
        if(self::$configs_path and is_readable(self::$configs_path)){
            if(Storage::mtime(PINDEX_PATH_CONFIG) > Storage::mtime(self::$configs_path)){
                Debugger::trace('Config changed, rebuild!');
                self::_buildLite()  or PindexException::throwing("Failed to rebuild expired lite config!");
            }
            Debugger::trace('Load config file from cache!');
            self::$_cache = include self::$configs_path;
        }elseif(self::$_config['AUTO_BUILD'] and !empty(self::$_config['AUTO_CLASS_LIST'])){
            Debugger::trace('Build unexist lite config!');
            self::_buildLite()  or PindexException::throwing("Failed to build lite config!");;
        }
        is_array(self::$_cache) or self::$_cache = [];
    }

    /**
     * @return bool
     */
    private static function _buildLite(){
        foreach (self::$_config['AUTO_CLASS_LIST'] as $clsnm){
            self::loadOuter($clsnm);
        }
        // Closure is not suggest in config file due to var_export could not do well with closure
        // it will be translated to 'method Closure::__set_state()'
        return Storage::write(self::$configs_path,'<?php return '.var_export(self::$_cache,true).';');
    }
    /**
     * parse config file into php array
     * @param string $path 配置文件的路径
     * @param string|null $type 配置文件的类型,参数为null时根据文件名称后缀自动获取
     * @param callable $parser 配置解析方法 有些格式需要用户自己解析
     * @return array
     */
    public static function parse($path,$type=null,callable $parser=null){
        isset($type) or $type = pathinfo($path, PATHINFO_EXTENSION);
        switch ($type) {
            case self::TYPE_PHP:
                $result = include $path;
                if(!is_array($result)){
                    $result = [];
                }
                break;
            case self::TYPE_INI:
                if(($result = parse_ini_file($path)) === false) $result = [];
                break;
            case self::TYPE_YAML:
                if(($result = yaml_parse_file($path)) === false) $result = [];
                break;
            case self::TYPE_XML:
                $result = (array)simplexml_load_file($path);
                break;
            case self::TYPE_JSON:
//                var_dump(json_decode(file_get_contents($path),true));exit();
                $result = json_decode(file_get_contents($path), true);
                break;
            default:
                $result = $parser?$parser($path):PindexException::throwing('无法解析配置文件');
        }
        return $result;
    }

//------------------------------------ class config -------------------------------------------------------------------------------------
    /**
     * get class config
     * @param string $clsnm class name
     * @return array
     * @throws PindexException
     */
    public static function load($clsnm){
        if(!isset(self::$_cache[$clsnm])){
            $outer = self::loadOuter($clsnm);
            $inner = self::loadInner($clsnm);
            $outer and $inner = array_merge($inner,$outer);
            self::$_cache[$clsnm] = $inner;
        }
        return self::$_cache[$clsnm];
    }

    /**
     * read the outer class config (instead of modifying the class self)
     * @param string $clsnm class name
     * @param array $replacement
     * @return array
     */
    private static function loadOuter($clsnm,$replacement=[]){
        $cname = Utils::constant($clsnm,'CONF_NAME',null);//outer constant name
        self::$_map[$cname] = $cname?$clsnm:$replacement;
        strpos('.', $cname) and $cname = str_replace('.', '/' ,$cname);
        $path = PINDEX_PATH_CONFIG."/{$cname}.php";
        return self::$_cache[$clsnm] = is_readable($path)?include $path:$replacement;
    }

    /**
     * @param $clsnm
     * @param array $replacement
     * @return array|mixed
     */
    private static function loadInner($clsnm,$replacement=[]){
        $config = Utils::constant($clsnm,'CONF_CONVENTION',null);
        return $config?$config:$replacement;
    }

//------------------------------------ user config of dynamic -------------------------------------------------------------------------------------

    /**
     * read the user-defined config in PINDEX_PATH_RUNTIME
     * @param string $identify config identify
     * @param mixed $replacement
     * @return array
     */
    public static function read($identify,$replacement=[]){
        $path = self::_id2path($identify,true);
        if(!$path) return $replacement;
        return include $path;
    }

    /**
     * write user-config to file
     * @param string $identify
     * @param array $config
     * @return bool
     */
    public static function write($identify,array $config){
        $path = self::_id2path($identify,false);
        return Storage::write($path,'<?php return '.var_export($config,true).';');
    }

    /**
     * 将配置项转换成配置文件路径
     * @param string $item 配置项
     * @param mixed $check 检查文件是否存在
     * @return false|string 返回配置文件路径，参数二位true并且文件不存在时返回null
     */
    private static function _id2path($item,$check=true){
        strpos($item,'.') and $item = str_replace('.','/',$item);
        $path = self::$_config['USER_CONFIG_PATH']."/{$item}.php";
        return !$check || is_readable($path)?$path:false;
    }
}