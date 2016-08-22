<?php
/**
 * Email: linzongho@gmail.com
 * Github: https://github.com/linzongho/Pindex
 * User: asus
 * Date: 8/22/16
 * Time: 9:10 AM
 */

const PINDEX_DEBUG_MODE_ON = true;
const PINDEX_PAGE_TRACE_ON = true;

include '../Pindex/engine.php';

Pindex::start([
    'CACHE_URL_ON'      => true,
]);
//Pindex::initout();

