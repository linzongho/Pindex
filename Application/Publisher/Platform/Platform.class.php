<?php
/**
 * Created by PhpStorm.
 * User: lich4ung
 * Date: 8/30/16
 * Time: 12:04 PM
 */

namespace Application\Publisher\Platform;

/**
 * Class Platform 平台抽象
 * @package Application\Publisher\Platform
 */
abstract class Platform {

    //平台相关
    /**
     * @var string 登录表单提交页面
     */
    protected $submit_addresss = '';
    protected $method         = 'post';

    //隐藏表单
    protected $form_hiddens = [];
    protected $form_username  = 'username';
    protected $form_password  = 'password';

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
     * 检查登录
     * @param string $username
     * @return bool
     */
    abstract public function check($username);

    /**
     * 登录
     * @param string $username
     * @param string $password
     * @return bool
     */
    abstract public function login($username,$password);

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



}