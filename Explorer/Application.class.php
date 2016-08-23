<?php
namespace {

    use Pindex\Core\Configger;

    define('ENTRY_NAME','explorer.php');
    @set_time_limit(600);//10min pathInfoMuti,search,upload,download...
    @ini_set('session.cache_expire', 600);

    $web_root = str_replace($_SERVER['SCRIPT_NAME'], '', __DIR__ . '/'.ENTRY_NAME) . '/';
    substr($web_root, -10) == ENTRY_NAME.'/' and $web_root =$_SERVER['DOCUMENT_ROOT'] . '/';//解决部分主机不兼容问题

    define('WEB_ROOT', $web_root);
    define('HOST', (Pindex\Util\SEK::isHttps() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/');
    define('BASIC_PATH', __DIR__ . '/');
    define('APPHOST', HOST . str_replace(WEB_ROOT, '', BASIC_PATH));//程序根目录
    define('LIB_DIR', BASIC_PATH . 'lib/');        //库目录
    define('FUNCTION_DIR', LIB_DIR . 'function/');        //函数库目录
    define('CLASS_DIR', LIB_DIR . 'class/');            //内目录
    define('CORER_DIR', LIB_DIR . 'core/');            //核心目录
    define('DATA_PATH', BASIC_PATH . 'data/');       //用户数据目录
    define('LOG_PATH', DATA_PATH . 'log/');         //日志目录
    define('USER_SYSTEM', DATA_PATH . 'system/');      //用户数据存储目录
    define('DATA_THUMB', DATA_PATH . 'thumb/');       //缩略图生成存放
    define('LANGUAGE_PATH', DATA_PATH . 'i18n/');        //多语言目录

    define('TEMPLATE', BASIC_PATH . 'View/');    //模版文件路径
    define('CONTROLLER_DIR', BASIC_PATH . 'Controller/'); //控制器目录
    define('MODEL_DIR', BASIC_PATH . 'Model/');        //模型目录

    define('STATIC_JS', 'app');  //_dev(开发状态)||app(打包压缩)
    define('STATIC_LESS', 'css');//less(开发状态)||css(打包压缩)
    define('STATIC_PATH', "./static/");//静态文件目录
//define('STATIC_PATH','http://static.kalcaddle.com/static/');//静态文件统分离,可单独将static部署到CDN
    //可以自定义【用户目录】和【公共目录】;移到web目录之外，可以使程序更安全, 就不用限制用户的扩展名权限了;
    define('USER_PATH', DATA_PATH . 'User/');        //用户目录
//自定义用户目录；需要先将data/User移到别的地方 再修改配置，例如：
//define('USER_PATH',   DATA_PATH .'/Library/WebServer/Documents/User');
    define('PUBLIC_PATH', DATA_PATH . 'public/');     //公共目录
//公共共享目录,读写权限跟随用户目录的读写权限 再修改配置，例如：
//define('PUBLIC_PATH','/Library/WebServer/Documents/Public/');
    //office服务器配置；默认调用的微软的接口，程序需要部署到外网。本地部署weboffice 引号内填写office解析服务器地址 形如:  http://---/view.aspx?src=
    define('OFFICE_SERVER', "https://view.officeapps.live.com/op/view.aspx?src=");

    include(FUNCTION_DIR . 'web.function.php');
    include(FUNCTION_DIR . 'file.function.php');
    include(FUNCTION_DIR . 'common.function.php');
    include(FUNCTION_DIR . 'util.function.php');

    include(CLASS_DIR . 'fileCache.class.php');
    include(CORER_DIR . 'Controller.class.php');
    include(CORER_DIR . 'Model.class.php');


    function stripslashes_deep($value){
        $value = is_array($value) ? array_map('stripslashes_deep', $value) : (isset($value) ? stripslashes($value) : null);
        return $value;
    }
    /**
     * GET/POST数据统一入口
     * 将GET和POST的数据进行过滤，去掉非法字符以及hacker code，返回一个数组
     * 注意如果GET和POST有相同的Key，POST优先
     *
     * @return array $_GET和$_POST数据过滤处理后的值
     */
    function parse_incoming(){
        global $_GET, $_POST,$_COOKIE;
        $_COOKIE = stripslashes_deep($_COOKIE);
        $_GET	 = stripslashes_deep($_GET);
        $_POST	 = stripslashes_deep($_POST);
        $return = array_merge($_GET,$_POST);
        $remote = array_get($return,0);
        $return['URLremote'] = explode('/',trim($remote[0],'/'));
        return $return;
    }
    $GLOBALS['in'] = parse_incoming();
    isset($GLOBALS['in']['PHPSESSID']) and session_id($GLOBALS['in']['PHPSESSID']);

    @session_start();
    session_write_close();//避免session锁定问题;之后要修改$_SESSION 需要先调用session_start()



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


    //加载用户自定义配置
    $setting_user = BASIC_PATH.'config/setting_user.php';
    if (file_exists($setting_user)) {
        include($setting_user);
    }


    $GLOBALS['config'] = Configger::parse(PINDEX_PATH_CONFIG.'/explorer/general.php');


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
    $GLOBALS['L']['kod_name'] = $setting['system_name'];
    $GLOBALS['L']['kod_name_desc'] = $setting['system_desc'];
    if (isset($setting['powerby'])) {
        $GLOBALS['L']['kod_power_by'] = $setting['powerby'];
    }

    $GLOBALS['config']['setting_system'] = $setting;//全局

}


namespace Explorer{
    /**
     * 程序路由处理类
     * 这里类判断外界参数调用内部方法
     */
    class Application {
        public $default_controller = 'desktop';	//默认的类名
        public $default_action = 'index';
        public $default_do = null;			//默认的方法名
//	public $sub_dir ='';				//控制器子目录
        public $model = '';				//控制器对应模型  对象。

        public function __construct($ctler='desktop',$actio='index'){

        }

        /**
         * 设置默认的类名
         * @param string $default_controller
         */
        public function setDefaultController($default_controller){
            $this -> default_controller = $default_controller;
        }

        /**
         * 设置默认的方法名
         * @param string $default_action
         * @return void
         */
        public function setDefaultAction($default_action){
            $this -> default_action = $default_action;
        }

        /**
         * 运行controller 的方法
         * @param $class , controller类名。
         * @param $function , 方法名
         * @return mixed
         */
        public function appRun($class,$function){
            $class_file = CONTROLLER_DIR .$class.'.class.php';
            if (!is_file($class_file)) {
                pr($class.' controller not exists!',1);
            }
            require_once $class_file;
            if (!class_exists($class)) {
                pr($class.' class not exists',1);
            }
            $instance = new $class();
            if (!method_exists($instance, $function)) {
                pr($function.' method not exists',1);
            }
            return $instance -> $function();
        }


        /**
         * 运行自动加载的控制器
         */
        private function autorun(){
            global $config;
            if (count($config['autorun']) > 0) {
                foreach ($config['autorun'] as $key => $var) {
                    $this->appRun($var['controller'],$var['function']);
                }
            }

        }

        /**
         * 调用实际类和方式
         */
        public function run(){
            $URI = $GLOBALS['in']['URLremote'];
            if (!isset($URI[0]) || $URI[0] == '') $URI[0] = $this->default_controller;
            if (!isset($URI[1]) || $URI[1] == '') $URI[1] = $this->default_action;
            define('ST',$URI[0]);
            define('ACT',$URI[1]);
            //自动加载运行类。
            $this->autorun();
            $this->appRun(ST,ACT);
        }
    }

}
