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

use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

$allowMethods = [
    'clearDb' => [
        'success' => 'База данных успешно очищена',
        'error' => 'Ошибка очистке БД'
    ],
    'fillDb' => [
        'success' => 'База данных успешно заполнена',
        'error' => 'Ошибка при заполнении БД'
    ],
];

$method = isset($_REQUEST['method']) ? $_REQUEST['method'] : '';

if (isset($allowMethods[$method])) {
    $method = $allowMethods[$method];
} else {
    $method = false;
}

// if ($_SERVER['REQUEST_METHOD']  == 'POST') {

//     $suffix = '';

//     if ($mode == 'manage') {
//         fn_set_notification('N', '', 'VCVXVX');
//         $method = $_REQUEST['method'];
//         $suffix = ".manage?price_parser=" . $method;
//     }

//     return array(CONTROLLER_STATUS_OK, 'price_parser' . $suffix);
// }

if ($mode == 'manage') {
    if ($method !== false) {
        fn_set_notification('N', '', $method['success']);
        $suffix = '.manage';
        return array(CONTROLLER_STATUS_OK, 'price_parser' . $suffix);
    }

    Registry::set('navigation.tabs.price_parser', array (
        'title' => __('price_parser'),
        'js' => true
    ));

    Tygh::$app['view']->assign('price_parser', array());
}