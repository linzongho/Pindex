<?php
/**
 * Created by PhpStorm.
 * User: lich4ung
 * Date: 8/30/16
 * Time: 12:04 PM
 */

namespace Application\Publisher\Platform;
use Application\Publisher\Util\HttpRequest;
use Pindex\Debugger;

defined('PATH_COOKIE') or define('PATH_COOKIE',dirname(__DIR__).'/Cookie/');

/**
 * Class Platform 平台抽象
 * @package Application\Publisher\Platform
 */
abstract class Platform {

    //平台相关
    /**
     * @var string 登录表单提交页面
     */
    protected $login_addresss = '';
    /**
     * @var string 提交方法
     */
    protected $login_method = 'post';
    /**
     * @var string 产品提交页面
     */
    protected $submit_address   = '';
    /**
     * @var string 产品提交方法
     */
    protected $submit_method    = 'post';

    //隐藏表单
    protected $form_hiddens = [];
    //显式表单
    protected $form_username  = 'username';
    protected $form_password  = 'password';
    protected $form_verifycode = '';//验证码

    //用户相关
    /**
     * @var string 登录用户名
     */
    protected $username = 'zhangyishang';
    /**
     * @var string 登录密码
     */
    protected $password = 'zhangyishang';
    /**
     * @var string 验证码
     */
    protected $verifycode = '';

    /**
     * Platform constructor.
     * 初始化对应平台的配置和用户的配置
     */
    public function __construct(){
        $config = array_merge($this->getPlatformSetting(),$this->getUserSetting());
        foreach ($config as $key => $val){
            if(is_array($this->$key)){
                $val and $this->$key = json_decode($val,true);
            }else{
                $this->$key = $val;
            }
        }
    }

    /**
     * @param bool $build
     * @return string
     */
    protected function buildLoginFields($build=false){
        $form = array(
            $this->form_username    => $this->username,
            $this->form_password    => $this->password,
        );
        if($this->form_verifycode) $form[$this->form_verifycode] = $this->verifycode;
        $form = array_merge($this->form_hiddens,$form);
        return $build?http_build_query($form):$form;
    }

    /**
     * 检查登录
     * @return bool
     */
    public function check(){


    }

    public function login() {
        $fields = $this->buildLoginFields(false);
        $mothod = $this->login_method;
        $response = $this->$mothod($this->login_addresss,$fields,false);

        Debugger::trace(array(
            'request_fields'    => $fields,
            'response'    => $response,
        ));
        return strpos($response,'Set-Cookie')?true:false;
    }

    /**
     * 获取平台配置
     * @return array
     */
    abstract protected function getPlatformSetting();

    /**
     * 获取用户配置
     * @return array
     */
    abstract protected function getUserSetting();



    private $error = '';

    /**
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @param string $error
     */
    public function setError($error)
    {
        $this->error = $error;
    }

    /**
     * 模拟POST请求
     *
     * @param string $url
     * @param array $fields
     * @return mixed
     *
     * Examples:
     * ```
     * HttpCurl::post('http://api.example.com/?a=123', array('abc'=>'123', 'efg'=>'567'));
     * HttpCurl::post('http://api.example.com/', '这是post原始内容', 'json');
     * 文件post上传
     * XX HttpCurl::post('http://api.example.com/', array('abc'=>'123', 'file1'=>'@/data/1.jpg'));
     * ```
     */
    protected function post($url, $fields) {
        return HttpRequest::post($url,$fields,$this->getCookie(),true);
    }

    /**
     * 平台+用户名作为cookie的标识符
     * @return string
     */
    private function getIdentify(){
        return md5(static::class.'__'.$this->username);
    }

    /**
     * @return false|string
     * @throws \Exception cookie目录不存在或者不可写时抛出异常
     */
    protected function getCookie(){
        $cookie = PATH_COOKIE.$this->getIdentify().'.cookie.txt';
        $dir = dirname($cookie);
        if(!is_dir($dir)){
            if(!mkdir($dir,0777,true)){
                throw new \Exception('创建cookie存放目录失败');
            }
        }
        if(!is_writable($dir)){
            if(!chmod($dir,0777)){
                throw new \Exception('为cookie存放目录添加写权限失败');
            }
        }
        Debugger::trace('[Cookie]'.$cookie);
        return $cookie;
    }

}