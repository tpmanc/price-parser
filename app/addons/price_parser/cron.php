<?php

use Tygh\Registry;

set_time_limit (0);
ini_set('memory_limit', -1);

$dbHost = Registry::get('config.db_host');
$dbUser = Registry::get('config.db_user');
$dbPass = Registry::get('config.db_password');
$dbName = Registry::get('config.db_name');

mysql_connect($dbHost, $dbUser, $dbPass);
mysql_select_db($dbName);
mysql_query("SET NAMES 'utf8';");
mysql_query("SET CHARACTER SET 'utf8';");
mysql_query("SET SESSION collation_connection = 'utf8_general_ci';");

define('ADDON_PATH', __DIR__);
require(ADDON_PATH . '/ModuleController.php');

\ModuleController::updatePrices(ADDON_PATH);