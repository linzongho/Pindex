<?php

/**
 * Github: https://github.com/linzongho/Pindex
 * Email:linzongho@gmail.com
 * User: asus
 * Date: 8/25/16
 * Time: 9:28 PM
 */
class UsersAR extends CActiveRecord {

    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{users}}';
    }
    public function primaryKey()
    {
        return 'uid';
        // 对于复合主键，要返回一个类似如下的数组
        // return array('pk1', 'pk2');
    }

}