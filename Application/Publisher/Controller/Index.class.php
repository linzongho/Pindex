<?php
/**
 * Created by PhpStorm.
 * User: lich4ung
 * Date: 8/30/16
 * Time: 12:54 PM
 */
namespace Controller;
use Platform\EC21;

include_once '../init.php';

class Index {

    public function index(){
        $platform = new EC21();
        $platform->login();
        $platform->submitProduct();
    }

}