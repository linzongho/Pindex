<?php
/**
 * Email: linzongho@gmail.com
 * Github: https://github.com/linzongho/Pindex
 * User: asus
 * Date: 8/22/16
 * Time: 10:59 AM
 */

namespace Pindex\Core;

class Gateway {

    protected static $_blacklist = [];

    protected static $_whitelist = [];

    public static function check($remoteip){}

    public static function addBlacklist($remoteip){}

    public static function removeBlacklist($remoteip){}

    public static function addWhitelist($remoteip){}

    public static function removeWhitelist($remoteip){}

}