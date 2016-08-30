<?php
/**
 * Created by PhpStorm.
 * User: lich4ung
 * Date: 8/30/16
 * Time: 12:54 PM
 */
namespace Application\Publisher\Controller;
use Application\Publisher\Platform\EC21;

class Index {

    private function getUsername(){
        return 'zhangyishang';
    }

    private function getPlatform(){
        return EC21::class;
    }

    public function index(){


        $password = md5('123456') . '1.d>|rWo@h\'5^{|bKa;/H~ptmSxP"51M';for ($i = 0; $i < 100; $i++) {$password = sha1($password);}echo $password;


//        $platform = $this->getPlatform();
//        $platform = new $platform();

    }

}