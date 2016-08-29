<?php
/**
 * Created by PhpStorm.
 * User: lnzhv
 * Date: 7/25/16
 * Time: 6:16 PM
 */

namespace Application\Admin\Controller;
use Application\Admin\Model\MemberModel;
use Pindex\Core\Controller;
use Pindex\Core\Logger;
use Shirley\Loginout;

class Publics extends Controller{

    public function register(){
        $this->display();
    }
    public function login($username='',$passwd='',$remember=false){
        $error = '';
        if(PINDEX_IS_POST){
            if(!$username or !$passwd){
                $error = '用户名或者密码不能为空';
            }else{
                $result = Loginout::login($username,$passwd,MemberModel::getInstance());
                $remember and Loginout::remember(ONE_WEEK);
//                \Pindex\println([
//                    $result,
//                    Loginout::check(),
//                ],true);
                if($result){
                    $this->redirect('/Admin/Index/index');
                }else{
                    Logger::write([$result,$username,$passwd,'login failed']);
                }
                $error = $result;
            }
        }
        Loginout::check() and $this->redirect('/Admin/Index/index');//已经登录的状态
        $this->assign('error',$error);
        $this->display();
    }
    public function lockScreen(){
        $this->display();
    }

    public function show404(){
        $this->display('404');
    }

    public function show500(){
        $this->display('500');
    }

    /**
     * 注销登录
     */
    public function logout(){
        Loginout::logout(new MemberModel()) and $this->redirect('/Admin/Publics/login');
    }

}