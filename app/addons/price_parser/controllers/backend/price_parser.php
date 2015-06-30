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
        'function' => '',
        'success' => 'База данных успешно очищена',
        'error' => 'Ошибка очистке БД'
    ],
    'fillDb' => [
        'function' => '',
        'success' => 'База данных успешно заполнена',
        'error' => 'Ошибка при заполнении БД'
    ],
    'downloadPrices' => [
        'function' => '',
        'success' => 'Загрузка прайс листов завершена',
        'error' => 'Ошибка при загрузке прайст листов'
    ],
    'updateCategories' => [
        'function' => '',
        'success' => 'Обновление категорий завершено',
        'error' => 'Ошибка при обновлении категорий'
    ],
    'updateProducts' => [
        'function' => '',
        'success' => 'Обновление товаров завершено',
        'error' => 'Ошибка при обновлении товаров'
    ],
    'updatePrices' => [
        'function' => '',
        'success' => 'Обновление цен завершено',
        'error' => 'Ошибка при обновлении цен'
    ],
    'updateAmounts' => [
        'function' => '',
        'success' => 'Обновление остатков завершено',
        'error' => 'Ошибка при обновлении остатков'
    ],
];

$method = isset($_REQUEST['method']) ? $_REQUEST['method'] : '';

if (isset($allowMethods[$method])) {
    $method = $allowMethods[$method];
} else {
    $method = false;
}

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

    Registry::get('view')->assign('price_parser', array());
}