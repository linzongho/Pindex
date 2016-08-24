<?php
/**
 * Email: linzongho@gmail.com
 * Github: https://github.com/linzongho/Pindex
 * User: asus
 * Date: 8/22/16
 * Time: 6:01 PM
 */
const PINDEX_DEBUG_MODE_ON = true;
const PINDEX_PAGE_TRACE_ON = true;

include '../Pindex/engine.php';
Pindex::init([
    'APP_NAME' => 'Explorer',
    'ROUTER_PARSER'     => null,
    'DISPATCH_HANDLER'  => null,
]);

Pindex::start();
