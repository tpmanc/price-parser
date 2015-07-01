<?php

set_time_limit (0);
ini_set('memory_limit', -1);

mysql_connect('127.0.0.1', 'root', '');
mysql_select_db('sc');
mysql_query("SET NAMES 'utf8';");
mysql_query("SET CHARACTER SET 'utf8';");
mysql_query("SET SESSION collation_connection = 'utf8_general_ci';");

define('ADDON_PATH', __DIR__);
require(ADDON_PATH . '/ModuleController.php');

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_download_price_lists(){
    $res1 = \ModuleController::downloadPrices(ADDON_PATH);
    $res2 = \ModuleController::extract(ADDON_PATH);
    return $res1 * $res2;
}

function fn_clear_db(){
    return \ModuleController::clearDatabase();
}

function fn_fill_db(){
    return \ModuleController::fillEmptyDatabase(ADDON_PATH);
}

function fn_update_categories(){
    return \ModuleController::updateCategories(ADDON_PATH);
}

function fn_update_products(){
    return \ModuleController::updateProducts(ADDON_PATH);
}

function fn_update_prices(){
    return \ModuleController::updatePrices(ADDON_PATH);
}

function fn_update_amounts(){
    return \ModuleController::updateAmounts(ADDON_PATH);
}
