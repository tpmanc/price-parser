<?php
/***************************************************************************
*                                                                          *
*   (c) 2004 Vladimir V. Kalynyak, Alexey V. Vinokurov, Ilya M. Shalnev    *
*                                                                          *
* This  is  commercial  software,  only  users  who have purchased a valid *
* license  and  accept  to the terms of the  License Agreement can install *
* and use this program.                                                    *
*                                                                          *
****************************************************************************
* PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
* "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
****************************************************************************/

set_time_limit (0);

mysql_connect('127.0.0.1', 'root', '');
mysql_select_db('sc');
mysql_query("SET NAMES 'utf8';");
mysql_query("SET CHARACTER SET 'utf8';");
mysql_query("SET SESSION collation_connection = 'utf8_general_ci';");

use Tygh\Registry;
use Tygh\Languages\Languages;
use Tygh\BlockManager\Block;

define('ADDON_PATH', __DIR__);
require(ADDON_PATH . '/ModuleController.php');

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_clear_db(){
    \ModuleController::clearDatabase();
}
