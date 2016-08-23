<?php
/**
 * Email: linzongho@gmail.com
 * Github: https://github.com/linzongho/Pindex
 * User: asus
 * Date: 8/23/16
 * Time: 5:17 PM
 */
namespace {
    //只有在本类加载进去的时候以下常量才会进入内存中
    const SITUATION_ADMIN = 'situation_of_background';
    const SITUATION_HOME = 'situation_of_forehandground';
}

namespace Shirley{

    use Pindex\Library\Cookie;
    use Pindex\Library\Session;
    use Pindex\PindexException;
    use Pindex\Util\Encrypt\Base64;


    interface LoginoutInterface {
        /**
         * 执行登录操作
         * @param string $username 用户名
         * @param string $password 密码
         * @return false|array 登录失败时返回false，登录成功时返回该账户信息
         */
        public function login($username,$password);

        /**
         * 执行登出操作
         * @return bool 是否成功登出
         */
        public function logout();

        /**
         * 获取登录错误信息
         * @return string|null
         */
        public function getLoginError();

    }

    /**
     * Class Loginout
     * 通用的登录登出工具
     * @package Shirley
     */
    class Loginout {

        /**
         * @var LoginoutInterface 登录登出器
         */
        private static $model = null;
        /**
         * @var array 用户的登录信息
         */
        private static $info = null;

        /**
         * 检查在该场景中用户是否处于登录状态
         * @static
         * @param string $situation 登录场景
         * @return bool
         */
        public static function check($situation){
            $status = Session::get($situation);//return null if not set
            if(!$status){
                //未登录时检查cookie中是否记录账户要求rememeber的未过期的信息
                $cookie = Cookie::get($situation);
                if($cookie){
                    $usrinfo = unserialize(Base64::decrypt($cookie, $situation));
                    Session::set($situation, $usrinfo);
                    return true;
                }
            }
            return $status?true:false;
        }

        /**
         * 获取当前登录的账户的信息
         * @static
         * @return array|null
         */
        public static function info(){
            return self::$info;
        }

        /**
         * 获取当前登录的账户名称
         * @static
         * @return mixed|null
         */
        public static function getUsername(){
            return isset(self::$info['username'])? self::$info['username'] : null;
        }

        /**
         * 执行登录操作
         * @static
         * @param string $username 用户名
         * @param string $password 密码
         * @param int $expire 记录时间，如果是0表示不记录
         * @param string $situation 登录场景
         * @param LoginoutInterface $model
         * @return true|string 登录成功时返回true，否则返回错误信息
         */
        public static function login($username,$password,$expire=0,$situation,LoginoutInterface $model=null){
            if(self::check($situation)){
                return '用户已经登录';
            }else{
                $model and self::$model = $model;
                if($model instanceof LoginoutInterface) {
                    $info = $model->login($username,$password);
                    if($info){
                        if($expire){
                            $sinfo = serialize($info);
                            $cookie = Base64::encrypt($sinfo, $situation);
                            Cookie::set($situation, $cookie, $expire);//一周的时间
                        }
                        Session::set($situation, self::$info = $info);
                        return true;
                    }else{
                        return $model->getLoginError();
                    }
                }else{
                    return PindexException::throwing('不存在可用的模型！');
                }
            }
        }

        /**
         * 注销登陆
         * @param string $situation 登录场景
         * @param LoginoutInterface $model
         * @return bool
         */
        public static function logout($situation,LoginoutInterface $model=null){
            $model and self::$model = $model;
            if($model instanceof LoginoutInterface) {
                if($model->logout()){
                    Session::delete($situation);
                    Cookie::clear($situation);
                    return true;
                }
            }
            return false;
        }

    }
}