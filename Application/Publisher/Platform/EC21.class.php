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
    protected $login_addresss = 'https://login.ec21.com/global/login/LoginSubmit.jsp';
    /**
     * @var string 产品提交页面
     */
    protected $submit_address   = 'http://www.ec21.com/global/basic/MyProductEditCheck.jsp';//global/basic/MyProductEditSubmit.jsp??


    public function getCategoryList(){
        $url = 'http://www.ec21.com/global/category/categoryMajorSelectGetData.jsp?actionName=category&step=';
        //step from 1 to 4 total 4level
    }

    //隐藏表单
    protected $form_hiddens = [
        'FBIn'  => '',
        'fEmail'  => '',
        'inq_gubun'  => '',
        'nextUrl'  => 'http://www.ec21.com/',
        'periodLimit'   => 'Y',
    ];
    //显式表单
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


    public function submitProduct(){
        $form = [
            'gcatalog_id'   => 'Products',//分組ID,default to 'Products'
            'catalog_nm'    => 'Light Booble',//Product Name
            'keyword1'      => 'Light',
            'keyword2'      => 'Booble',
            'keyword3'      => 'Booble2',
            'keyword4'      => 'Booble3',
            // ** 分類ID 以及分類名稱 **
            'categorymId'   => '070709',
            'categoryNm'    => 'Faucets, Mixers & Taps',  //分類ID

            //Attribute
            'origin'    => 'CN',//Place of Origin

                'allDesc'   => 'Faucets, Mixers & TapsFaucets, Mixers & TapsFaucets, Mixers & TapsFaucets, Mixers & TapsFaucets, Mixers & TapsFaucets, Mixers & TapsFaucets, Mixers & TapsFaucets, Mixers & TapsFaucets, Mixers & TapsFaucets, Mixers & TapsFaucets, Mixers & TapsFaucets, Mixers & TapsFaucets, Mixers & TapsFaucets, Mixers & TapsFaucets, Mixers & TapsFaucets, Mixers & TapsFaucets, Mixers & TapsFaucets, Mixers & TapsFaucets, Mixers & TapsFaucets, Mixers & TapsFaucets, Mixers & Taps',//detail
            'display'   => 'Y',
        ];
        $method = $this->submit_method ;
        $result = $this->$method($this->submit_address,$form);
        echo $result;
    }

    protected function getPlatformSetting() {
        return [];
    }

    protected function getUserSetting()
    {
        return [];
    }


    public function submit(){

    }

}