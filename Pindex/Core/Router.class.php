<?php
/**
 * Email: linzongho@gmail.com
 * Github: https://github.com/linzongho/Pindex
 * User: asus
 * Date: 8/22/16
 * Time: 10:55 AM
 */
namespace Pindex\Core;
use Pindex\Lite;

interface URLParseCreater {
    /**
     * 解析URL或兼域名
     * @return bool
     */
    public function parse();

    /**
     * 创建URL
     * @param string|array $modules 模块序列
     * @param string $contler 控制器名称
     * @param string $action 操作名称
     * @param array|null $params 参数
     * @return string 可以访问的URI
     */
    public function create($modules,$contler,$action,array $params=null);

    /**
     * 获取解析的模块，多个模块使用'/'分隔
     * @return string
     */
    public function getModules();

    /**
     * 获取控制器
     * @return string
     */
    public function getController();

    /**
     * 获取操作名称
     * @return string
     */
    public function getAction();

}


class Router extends Lite{

    const CONF_NAME = 'route';
    const CONF_CONVENTION = [
        'DRIVER_DEFAULT_INDEX' => 0,//默认驱动ID，类型限定为int或者string
        'DRIVER_CLASS_LIST' => [
            'Pindex\\Core\\Router\\LiteRouter',
        ],//驱动类的列表
        'DRIVER_CONFIG_LIST' => [
            [
                //@see LiteRouter's config
            ]
        ],//驱动类列表参数
    ];

    /**
     * @static
     * @param URLParseCreater|null $parser
     * @param array|null $cparam
     * @return bool
     */
    public static function parse(URLParseCreater $parser=null,array $cparam=null){
        if($parser){
            if(class_exists($parser,true)){
                $parser = new $parser($cparam);
                return $parser->parse();
            }else{

            }
        }
        return self::driver()->parse();
    }

    public static function create($modules,$contler,$action,array $params=null,URLParseCreater $parser=null){
        if($parser){
            return $parser->create($modules,$contler,$action,$params);
        }
        return self::driver()->create($modules,$contler,$action,$params);
    }

    /**
     * $url规则如：
     *  .../Ma/Mb/Cc/Ad
     * 依次从后往前解析出操作，控制器，模块(如果存在模块将被认定为完整的模块路径)
     * @param string $url 快速创建的URL字符串
     * @param array $params GET参数数组
     * @return string
     */
    public static function url($url=null,array $params=[]){
        //解析参数中的$url
        empty($params) and $params = [];
        if(!$url){
            return self::create(null,null,null,$params);
        }
        $hashpos = strpos($url,'#');
        if($hashpos){
            $hash = substr($url,$hashpos+1);
            $url = substr($url,0,$hashpos);
        }else{
            $hash = '';
        }
        $parts = @explode('/',trim($url,'/'));

        //调用URLHelper创建URL
        $action  = array_pop($parts);
        $ctler   = $action?array_pop($parts):null;
        $modules = $ctler?$parts:null;
        $url = self::create($modules,$ctler,$action,$params);
//        \Pindex\dumpout($modules,$ctler,$action,$url);
        if($hash) $url .= '#'.$hash;
        return $url;
    }

    /**
     * 重定向
     * @param string $url 重定向地址
     * @param int $time
     * @param string $message
     * @return void
     */
    public static function redirect($url,$time=0,$message=''){
        //多行URL地址支持
        $url = str_replace(['\n','\r'], '', $url);
        $message or $message = "系统将在{$time}秒之后自动跳转到{$url}！";

        if(headers_sent()){//检查头部是否已经发送
            exit("<meta http-equiv='Refresh' content='{$time};URL={$url}'>{$message}");
        }else{
            if(0 === $time){
                header('Location: ' . $url);
            }else{
                header("refresh:{$time};url={$url}");
                exit($message);
            }
        }
    }
}