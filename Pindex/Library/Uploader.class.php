<?php
/**
 * Email: linzongho@gmail.com
 * Github: https://github.com/linzongho/Pindex
 * User: asus
 * Date: 8/22/16
 * Time: 11:48 AM
 */

namespace Pindex\Library;

use Pindex\Lite;

interface UploaderInterface {


    /**
     * 检测上传根目录
     * @param string $rootpath   根目录
     * @return boolean true-检测通过，false-检测失败
     */
    public function checkRootPath($rootpath);

    /**
     * 检测上传目录
     * @param  string $savepath 上传目录
     * @return boolean          检测结果，true-通过，false-失败
     */
    public function checkSavePath($savepath);

    /**
     * 保存指定文件
     * @param  array   $file    保存的文件信息
     * @param  boolean $replace 同名文件是否覆盖
     * @return boolean          保存状态，true-成功，false-失败
     */
    public function save($file, $replace=true);

    /**
     * 获取最后一次上传错误信息
     * @return string 错误信息
     */
    public function getError();


}
class Uploader extends Lite{

    const CONF_NAME = 'uploader';
    const CONF_CONVENTION = [
        'DRIVER_DEFAULT_INDEX' => 0,//默认驱动ID，类型限定为int或者string
        'DRIVER_CLASS_LIST' => [
            'Pindex\\Library\\Uploader\\Local',
        ],//驱动类的列表
        'DRIVER_CONFIG_LIST' => [],//驱动类列表参数

        'mimes'         =>  [], //允许上传的文件MiMe类型
        'maxSize'       =>  0, //上传的文件大小限制 (0-不做限制)
        'exts'          =>  [], //允许上传的文件后缀
        'autoSub'       =>  true, //自动子目录保存文件
        'subName'       =>  null, //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
        'rootPath'      =>  PINDEX_PATH_BASE.'/Public/upload/', //保存根路径
        'savePath'      =>  '/', //保存路径
        'saveName'      =>  ['uniqid', ''], //上传文件命名规则，[0]-函数名，[1]-参数，多个参数使用数组
        'saveExt'       =>  '', //文件保存后缀，空则使用原后缀
        'replace'       =>  false, //存在同名是否覆盖
        'hash'          =>  true, //是否生成hash编码
        'driver'        =>  '', // 文件上传驱动
        'driverConfig'  =>  [], // 上传驱动配置
        'FILE_UPLOAD_TYPE'      =>  'Local',    // 文件上传方式
        'UPLOAD_TYPE_CONFIG'    =>  [],
    ];
    /**
     * 默认上传配置
     * @var array
     */
    private $config = [];

    /**
     * 上传错误信息
     * @var string
     */
    private $error = ''; //上传错误信息

    /**
     * 上传驱动实例
     * @var UploaderInterface
     */
    protected $_driver = null;

    /**
     * 构造方法，用于构造上传实例
     * Uploader constructor.
     * @param null $identify
     */
    public function __construct($identify=null){
        $this->config = self::getConfig();
        $this->apply();
        $this->_driver = self::driver();
    }

    private function apply($config=null){
        $config and $this->config = array_merge($this->config,$config);

        /* 调整配置，把字符串配置参数转换为数组 */
        if(!empty($this->config['mimes'])){
            if(is_string($this->config['mimes'])) {
                $this->config['mimes'] = explode(',', $this->config['mimes']);
            }
            $this->config['mimes'] = array_map('strtolower', $this->config['mimes']);
        }
        if(!empty($this->config['exts'])){
            if (is_string($this->config['exts'])){
                $this->config['exts'] = explode(',', $this->config['exts']);
            }
            $this->config['exts'] = array_map('strtolower', $this->config['exts']);
        }
    }

    /**
     * 获取最后一次上传错误信息
     * @return string 错误信息
     */
    public function getError(){
        return $this->error;
    }

    /**
     * 上传单个文件
     * @param  array  $file 文件数组
     * @return array        上传成功后的文件信息
     */
    public function uploadOne($file){
        $info = $this->upload([$file]);
        return $info ? $info[0] : $info;
    }

    /**
     * 上传文件
     *  文件信息数组，通常是 $_FILES数组
     *
     * @param string|null $upload_path file upload path,oppside to 'rootPath'
     * @return array|bool
     */
    public function upload($upload_path=null) {
        $files = $_FILES;
        if(!$files){
            $this->error = '没有上传的文件！';
            return false;
        }

        if(is_array($upload_path)){
            $this->apply($upload_path);
        }else if($upload_path){
            $this->config['savePath'] = trim($upload_path,'/\\').'/';
        }

        /* 检测上传根目录 */
        if(!$this->_driver->checkRootPath($this->config['rootPath'])){
            $this->error = $this->_driver->getError();
            return false;
        }

        /* 检查上传目录 */
        $savepath = $this->config['rootPath'].$this->config['savePath'];
        if(!$this->_driver->checkSavePath($savepath)){
            $this->error = $this->_driver->getError();
            return false;
        }

        /* 逐个检测并上传文件 */
        $info   = [];;
        $finfo  = null;
        if(function_exists('finfo_open')){
            $finfo   =  finfo_open ( FILEINFO_MIME_TYPE );
        }
//        \Pindex\dumpout($finfo,$files);
        foreach ($this->dealFiles($files) as $key => $file) {
            $file['name']  = strip_tags($file['name']);
//            \Pindex\dumpout($savepath);
            $file['savepath'] = $savepath;
            if(!isset($file['key']))   $file['key']    =   $key;
            /* 通过扩展获取文件类型，可解决FLASH上传$FILES数组返回文件类型错误的问题 */
            $finfo and $file['type']   =   finfo_file ( $finfo ,  $file['tmp_name'] );

            /* 获取上传文件后缀，允许上传无后缀文件 */
            $file['ext']    =   pathinfo($file['name'], PATHINFO_EXTENSION);

            /* 文件上传检测 */
            if (!$this->check($file)){
                continue;
            }

            /* 获取文件hash */
            if($this->config['hash']){
                $file['md5']  = md5_file($file['tmp_name']);
                $file['sha1'] = sha1_file($file['tmp_name']);
            }

            /* 生成保存文件名 */
            $savename = $this->getSaveName($file);
            if(false == $savename){
                continue;
            } else {
                $file['savename'] = $savename;
            }

            /* 对图像文件进行严格检测 */
            $ext = strtolower($file['ext']);
            if(in_array($ext, array('gif','jpg','jpeg','bmp','png','swf'))) {
                $imginfo = getimagesize($file['tmp_name']);
                if(empty($imginfo) || ($ext == 'gif' && empty($imginfo['bits']))){
                    $this->error = '非法图像文件！';
                    continue;
                }
            }

            /* 保存文件 并记录保存成功的文件 */
            if ($this->_driver->save($file,$this->config['replace'])) {
                unset($file['error'], $file['tmp_name']);
                $info[$key] = $file;
            } else {
                $this->error = $this->_driver->getError();
            }
        }
        $finfo and finfo_close($finfo);
        if(!empty($info['download'])){
            if(strpos($savepath,PINDEX_PATH_PUBLIC) === 0){
                $info['download']['savepath'] = substr($savepath,strlen(PINDEX_PATH_PUBLIC)-1);
            }
            // the url whose cound be access
            $info['download']['access_url'] = PINDEX_PUBLIC_URL.$info['download']['savepath'].$info['download']['savename'];
        }
        return empty($info) ? false : $info;
    }

    /**
     * 转换上传文件数组变量为正确的方式
     * <code>
     * array (
     *  'name' => '21.gif',
     *  'type' => 'application/octet-stream',
     *  'tmp_name' => '/tmp/phpwJyyVm',
     *  'error' => 0,
     *  'size' => 1119,
     * ),
     * </code>
     * @access private
     * @param array $files  上传的文件变量
     * @return array
     */
    private function dealFiles($files) {
//        \Pindex\dump($_FILES);
        $fileArray  = [];
        $n          = 0;
        foreach ($files as $index=>$file){
            if(is_array($file['name'])) {/* multidimensional to one dimensional  */
                $keys       =   array_keys($file);
                $count      =   count($file['name']);
                for ($i=0; $i<$count; $i++) {
                    $fileArray[$n]['key'] = $index;
                    foreach ($keys as $_key){
                        $fileArray[$n][$_key] = $file[$_key][$i];
                    }
                    $n++;
                }
            }else{
                $fileArray = $files;
                break;
            }
        }
//        \Pindex\dumpout($fileArray);
        return $fileArray;
    }


    /**
     * 检查上传的文件
     * @param array $file 文件信息
     * @return bool
     */
    private function check($file) {
        /* 文件上传失败，捕获错误代码 */
        if ($file['error']) {
            $this->error($file['error']);
            return false;
        }

        /* 无效上传 */
        if (empty($file['name'])){
            $this->error = '未知上传错误！';
        }

        /* 检查是否合法上传 */
        if (!is_uploaded_file($file['tmp_name'])) {
            $this->error = '非法上传文件！';
            return false;
        }

        /* 检查文件大小 */
        if (!$this->checkSize($file['size'])) {
            $this->error = '上传文件大小不符！';
            return false;
        }

        /* 检查文件Mime类型 */
        //:FLASH上传的文件获取到的mime类型都为application/octet-stream
        if (!$this->checkMime($file['type'])) {
            $this->error = '上传文件MIME类型不允许！';
            return false;
        }

        /* 检查文件后缀 */
        if (!$this->checkExt($file['ext'])) {
            $this->error = '上传文件后缀不允许';
            return false;
        }

        /* 通过检测 */
        return true;
    }


    /**
     * 获取错误代码信息
     * @param string $errorNo  错误号
     */
    private function error($errorNo) {
        switch ($errorNo) {
            case 1:
                $this->error = '上传的文件超过了 php.ini 中 upload_max_filesize 选项限制的值！';
                break;
            case 2:
                $this->error = '上传文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值！';
                break;
            case 3:
                $this->error = '文件只有部分被上传！';
                break;
            case 4:
                $this->error = '没有文件被上传！';
                break;
            case 6:
                $this->error = '找不到临时文件夹！';
                break;
            case 7:
                $this->error = '文件写入失败！';
                break;
            default:
                $this->error = '未知上传错误！';
        }
    }

    /**
     * 检查文件大小是否合法
     * @param integer $size 数据
     * @return bool
     */
    private function checkSize($size) {
        return !($size > $this->config['maxSize']) || (0 == $this->config['maxSize']);
    }

    /**
     * 检查上传的文件MIME类型是否合法
     * @param string $mime 数据
     * @return bool
     */
    private function checkMime($mime) {
        return empty($this->config['mimes']) ? true : in_array(strtolower($mime), $this->config['mimes']);
    }

    /**
     * 检查上传的文件后缀是否合法
     * @param string $ext 后缀
     * @return bool
     */
    private function checkExt($ext) {
        return empty($this->config['exts']) ? true : in_array(strtolower($ext), $this->config['exts']);
    }

    /**
     * 根据上传文件命名规则取得保存文件名
     * @param string $file 文件信息
     * @return bool
     */
    private function getSaveName($file) {
        $rule = $this->config['saveName'];
        if (empty($rule)) { //保持文件名不变
            /* 解决pathinfo中文文件名BUG */
            $filename = substr(pathinfo("_{$file['name']}", PATHINFO_FILENAME), 1);
            $savename = $filename;
        } else {
            $savename = $this->getName($rule, $file['name']);
            if(empty($savename)){
                $this->error = '文件命名规则错误！';
                return false;
            }
        }

        /* 文件保存后缀，支持强制更改文件后缀 */
        $ext = empty($this->config['saveExt']) ? $file['ext'] : $this->config['saveExt'];

        return $savename . '.' . $ext;
    }

    /**
     * 根据指定的规则获取文件或目录名称
     * @param  array  $rule     规则
     * @param  string $filename 原文件名
     * @return string           文件或目录名称
     */
    private function getName($rule, $filename){
        $name = '';
        if(is_array($rule)){ //数组规则
            $func     = $rule[0];
            $param    = (array)$rule[1];
            foreach ($param as &$value) {
                $value = str_replace('__FILE__', $filename, $value);
            }
            $name = call_user_func_array($func, $param);
        } elseif (is_string($rule)){ //字符串规则
            if(function_exists($rule)){
                $name = call_user_func($rule);
            } else {
                $name = $rule;
            }
        }
        return $name;
    }

}