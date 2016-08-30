<?php
/**
 * Created by PhpStorm.
 * User: lich4ung
 * Date: 8/30/16
 * Time: 12:34 PM
 */

namespace Application\Publisher\Platform;

/**
 * Class EC21
 * http://www.ec21.com/
 * @package Application\Publisher\Platform
 */
class EC21 extends Platform {

    /**
     * @var string 登录表单提交页面
     */
    protected $submit_addresss = 'https://login.ec21.com/global/login/Login.jsp';
    protected $method         = 'post';

    //隐藏表单
    protected $form_hiddens = [
        'nextUrl'  => 'http://www.ec21.com/',
        'inq_gubun'  => '',
        'FBIn'  => '',
        'fEmail'  => '',
        'periodLimit'   => 'Y',
    ];

    protected $form_username  = 'user_id';
    protected $form_password  = 'user_pw';

    //用户相关
    /**
     * @var string 登录用户名
     */
    protected $username = 'zhangyishang';
    /**
     * @var string 登录密码
     */
    protected $password = 'zhangyishang';


    public function check($username) {
    }

    public function login($username, $password) {

        get_headers();
    }

    protected function getPlatformSetting()
    {
        return [];
    }

    protected function getUserSetting()
    {
        return [];
    }

}