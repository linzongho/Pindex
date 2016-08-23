<?php

/**
 * Created by PhpStorm.
 * User: lnzhv
 * Date: 7/25/16
 * Time: 5:49 PM
 */
namespace Application\Admin\Model;
use Pindex\Core\Logger;
use Pindex\Core\Model;
use Pindex\Library\Cookie;
use Pindex\Library\Session;
use Pindex\Util\Encrypt\Base64;
use Pindex\Util\Helper\ClientAgent;
use Shirley\LoginoutInterface;

class MemberModel extends Model implements LoginoutInterface{

    protected $tablename = 'lx_member';

    /**
     * 获取登录错误信息
     * @return string|null
     */
    public function getLoginError()
    {
        // TODO: Implement getLoginError() method.
    }

    protected $fields = [
        'username'  => null,
        'sex'       => null,
        'nickname'  => null,
        'email'     => null,
        'reg_time'  => null,
        'last_login_ip'     => null,
        'last_login_time'   => null,
        'status'    => null,
        'passwd'    => null,//初始密码
        'profile'   => null,
    ];

    const LOGIN_USERNAME = 0;
    const LOGIN_EMAIL = 1;

    /**
     * 状态标记
     */
    const USER_INFO_FLAG = '_userinfo_';
    const USER_INFO_KEY = 'dhasdjksahdh324r89r3h28dfhj322ur';
    /**
     * 登录的用户信息
     * @var array
     */
    private static $_userinfo = [];

    /**
     * @param string $username
     * @param null $password
     * @param int $expire
     * @return bool
     */
    public function login($username,$password,$expire=0){
        $usrinfo = $this->checkLogin($username,$password);
        if($usrinfo){
            if($expire){
                $sinfo = serialize($usrinfo);
                $cookie = Base64::encrypt($sinfo, self::USER_INFO_FLAG);
                Cookie::set(self::USER_INFO_FLAG, $cookie,$expire);//一周的时间
            }
            Session::set(self::USER_INFO_FLAG, self::$_userinfo = $usrinfo);
            return true;
        }
        return false;
    }

    /**
     * 获取登录信息
     * @param string $name 信息名称
     * @return array|false|null 发生了错误时返回FALSE
     */
    public function getLoginInfo($name=null){
        if(!self::$_userinfo){
            self::$_userinfo = Session::get(self::USER_INFO_FLAG);
            if(null === self::$_userinfo){
                //用户未登录,按照情况执行抛出异常操作或者返回null
                return false;//'用户未登录，无法执行该操作！'
            }
        }

        if($name){
            return isset($info[$name])?$info[$name]:null;
        }
        return self::$_userinfo;
    }

    /**
     * 注销登陆
     * @return void
     */
    public function logout(){
        Session::delete(self::USER_INFO_FLAG);
        Cookie::clear(self::USER_INFO_FLAG);
    }

    /**
     * 检查登陆
     * @param string $account 账户名称，可以是用户名、邮箱和手机号
     * @param string $password 账号密码，必须和数据库密码一致
     * @param int $type
     * @return false|array 返回false时表示登陆失败，可以通过error方法获取错误信息
     */
    private function checkLogin($account, $password, $type=self::LOGIN_USERNAME){
        $where = ['status'=>1];//only status =1
        switch ($type){
            case self::LOGIN_EMAIL:
                $where['email'] = $account;
                break;
            case self::LOGIN_USERNAME:
            default:
                $where['username'] = $account;
        }
        $userinfo = $this->fields('profile,email,id,nickname,last_login_ip,last_login_time,sex,username,passwd')->where($where)->find();
        if(false === $userinfo){
            Logger::write($this->error(),$userinfo);
            if(!PINDEX_DEBUG_MODE_ON){
                $this->error = '服务端发生了错误！';
            }
        }elseif(!$userinfo){//空数组
            $this->error = '用户不存在';
        }else{
            if($password === $userinfo['passwd']){
                //update
                $this->fields([
                    'last_login_ip'     => ClientAgent::getClientIP(),
                    'last_login_time'   => PINDEX_REQUEST_TIME,
                ])->where($where)->update();

                unset($userinfo['passwd']);
                return $userinfo;
            }else{
                $this->error = '密码不正确！';
            }
        }
        return false;
    }

    /**
     * 获取用户列表
     * @param int $status
     * @return array|bool
     */
    public function lists($status =  1){
        return $this->where('status = '.intval($status))->select();
    }

    /**
     * 根据用户名获取用户信息
     * @param $username
     * @return bool|mixed
     */
    public function findByName($username){
        $result = $this->where(['username'=>$username])->find();
        return $result;
    }
    /**
     * 添加用户
     * @param array $info
     * @return bool
     */
    public function add(array $info){
        $info = $this->data($info);
        if(empty($info['nickname'])) $info['nickname'] = '匿名用户_'.str_replace('.','',''.microtime(true));
        return $this->fields($info)->create();
    }

    /**
     * 删除用户
     * @param int $uid
     * @return bool
     */
    public function remove($uid){
        return $this->fields(['status'=>0])->where('id = '.intval($uid))->update();
    }

    /**
     * 修改用户信息
     * @param array $info
     * @return bool
     */
    public function revise(array $info){
        if(!isset($info['id'])){
            $this->error = '缺少用户ID信息，无法完成更新';
            return false;
        }
        $id = $info['id'];
        unset($info['id']);
        $info = $this->data($info);
        return $this->fields($info)->where('id = '.intval($id))->update();
    }


}