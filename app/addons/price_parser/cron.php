<?php

define('ADDON_PATH', __DIR__);
define('AREA', 'C');

$str = ADDON_PATH;
$len = strpos($str, '/app/');
$str = mb_strcut($str, 0, $len); // get csm folder path

try {
    require($str . '/init.php');
    fn_dispatch();
} catch (Tygh\Exceptions\AException $e) {
    $e->output();
}

require(ADDON_PATH . '/ModuleController.php');

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

$q = mysql_query('SELECT value
                  FROM cscart_settings_objects
                  WHERE name="productPriceField" AND section_id =
                      (SELECT section_id
                      FROM cscart_settings_sections
                      WHERE name="price_parser" LIMIT 1)');
if ($q === false) {
    die('cant rear price filed');
}
$priceField = mysql_result($q, 0);

var_dump(\ModuleController::updatePrices(ADDON_PATH, $priceField));