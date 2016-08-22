<?php
/**
 * Email: linzongho@gmail.com
 * Github: https://github.com/linzongho/Pindex
 * User: asus
 * Date: 8/22/16
 * Time: 2:15 PM
 */


namespace Application\Test\Controller;
use Pindex\Core\Cache;
class Index {

    public function index(){


        $this->cache();
    }

    public function testTrait(){

    }

    public function cache(){
//        $r1 = Cache::get('1232131',null);
//        if(null !== $r1){
//            echo "from cache
//        $r1";
//        }else{
//            echo Cache::set('1232131','thdshdisahdioashduiwqfndsdbuhwefwf',10)?'cache ok':' cache failed';
//        }

//        ob_start();
//        echo ob_get_level().'Hello Testing!';
//        ob_start();
//        echo ob_get_level().'Hello Testing2!';
//        $content = ob_get_clean();
//        $content2 = ob_get_clean();
//
//        \Pindex\dumpout($content,$content2);

        if(Cache::has('111') and Cache::has('222')){
            $r1 = Cache::get('111',null);
            $r2 = Cache::get('222',null);

            \Pindex\dumpout('FROM CACHE',$r1,$r2);
        }else{
            Cache::begin('111');
            echo ob_get_level().'Hello Testing111!';
            Cache::begin('222');
            echo ob_get_level().'Hello Testing222!';
            $r1 = Cache::end(10,'222');
            $r2 = Cache::end(10,'111');
            \Pindex\dumpout('BUILD CACHE',$r1,$r2);
        }
    }

}