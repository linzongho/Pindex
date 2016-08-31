<?php
/**
 * Created by PhpStorm.
 * User: lich4ung
 * Date: 8/30/16
 * Time: 12:54 PM
 */
namespace Application\Publisher\Controller;
use Application\Publisher\Platform\EC21;

spl_autoload_register(function ($clsnm){
    //TODO:以本目录建立类地图
});


class Index {

    private function getUsername(){
        return 'zhangyishang';
    }

    private function getPlatform(){
        return EC21::class;
    }

    public function index(){
//        $platform = $this->getPlatform();
//        $platform = new $platform();

        $platform = new EC21();
        $platform->login();

        $platform->submitProduct();

    }


}