<?php
/**
 * Email: linzongho@gmail.com
 * Github: https://github.com/linzongho/Pindex
 * User: asus
 * Date: 8/22/16
 * Time: 11:09 AM
 */

namespace Pindex\Core;
use Pindex\Lite;

/**
 * Interface CacheInterface 缓存驱动接口
 * @package Kbylin\System\Library\Cache
 */
interface CacheInterface {
    /**
     * 读取缓存
     * @access public
     * @param string $name 缓存变量名
     * @param mixed $replacement
     * @return mixed
     */
    public function get($name,$replacement=null);

    /**
     * @access public
     * @param string $name 缓存名称
     * @return bool
     */
    public function has($name);

    /**
     * 写入缓存
     * @access public
     * @param string $name 缓存变量名
     * @param mixed $value  存储数据
     * @param int $expire  有效时间，0为永久（以秒计时）
     * @return boolean
     */
    public function set($name, $value, $expire = null);

    /**
     * 删除缓存
     * @access public
     * @param string $name 缓存变量名
     * @return boolean
     */
    public function delete($name);

    /**
     * 清除缓存
     * @access public
     * @return boolean
     */
    public function clean();
}

/**
 * Class Cache
 *
 * @method bool has(string $name) static 判断缓存是否存在
 * @method int get(string $name,$replace=null) static 读取缓存
 * @method boolean set(string $name,mixed $value,int $expire=null) static 写入缓存
 * @method int delete(string $name) static 删除缓存
 * @method int clean() static empty the cache
 * @package Pindex\Core
 */
class Cache extends Lite{

    const CONF_NAME = 'cache';
    const CONF_CONVENTION = [
        DRIVER_DEFAULT_INDEX => 0,
        DRIVER_CLASS_LIST => [
            'Pindex\\Core\\Cache\\File',
            'Pindex\\Core\\Cache\\Memcache',
        ],
        DRIVER_CONFIG_LIST => [
            [
                //from thinkphp ,match case
                'expire'        => 0,
                'cache_subdir'  => false,
                'path_level'    => 1,
                'prefix'        => '',
                'length'        => 0,
                'path'          => PINDEX_PATH_RUNTIME.'/file_cache/',
                'data_compress' => false,
            ],
            [
                'host'      => 'localhost',
                'port'      => 11211,
                'expire'    => 0,
                'prefix'    => '',
                'timeout'   => 1000, // 超时时间（单位：毫秒）
                'persistent'=> true,
                'length'    => 0,
            ],
        ],
        //5分钟
        'DEFAULT_CACHE_EXPIRE'  => 300,
    ];
    /**
     * @var array id堆栈
     */
    public static $idStack = [];

    private static $error = null;

    public static function getError(){
        return self::$error;
    }

    /**
     * 缓存开始记录标记
     * @static
     * @param $identify
     * @return void
     */
    public static function begin($identify){
        ob_start();
        $level = ob_get_level();
        self::$idStack[$level] = $identify;
    }

    /**
     * 保存该level的数据成缓存
     * @static
     * @param int $expire 缓存时间，建议在10秒钟到1天之间
     * @param string|int $id4check 检查是否是该level的identifdy，如果不是则不保存
     * @return false|$content 返回缓存的内容或者false时表示发生了错误，可以使用getError方法获取错误信息
     */
    public static function end($expire=null,$id4check=null){
        $level = ob_get_level();
        if($level){
            if(isset(self::$idStack[$level])){
                $identify = self::$idStack[$level];
                if($id4check and $id4check !== $identify){
                    self::$error = "输入的检查项'{$id4check}'不同于LEVEL-{$level}的缓存项ID '{$identify}'，请确认!";
                    return false;
                }else{
                    $content = ob_get_clean();
                    $expire or $expire = self::getConfig('DEFAULT_CACHE_EXPIRE',3600);
                    self::set($identify,$content,$expire);
                    return $content;
                }
            }else{
                self::$error = "LEVEL为'{$level}'的记录不存在于缓存栈中，OB缓存可能通过其他方式开启并且在为手动关闭的情况下调用endWith方法！";
                return false;
            }
        }else{
            self::$error = 'OB缓存未处于开启状态！';
            return false;
        }
    }

}