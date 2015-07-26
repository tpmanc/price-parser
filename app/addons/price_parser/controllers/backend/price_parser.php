<?php

use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

$allowMethods = [
    'clearDb' => [
        'function' => 'fn_clear_db',
        'success' => 'База данных успешно очищена',
        'error' => 'Ошибка очистке БД'
    ],
    'updateProperties' => [
        'function' => 'fn_update_properties',
        'success' => 'Характеристики товаров обновлены',
        'error' => 'Ошибка при обновлении характеристик'
    ],
    'fillDb' => [
        'function' => 'fn_fill_db',
        'success' => 'База данных успешно заполнена',
        'error' => 'Ошибка при заполнении БД'
    ],
    'downloadPrices' => [
        'function' => 'fn_download_price_lists',
        'success' => 'Загрузка прайс листов завершена',
        'error' => 'Ошибка при загрузке прайст листов. Проверьте права на папку temp/.'
    ],
    'updateCategories' => [
        'function' => 'fn_update_categories',
        'success' => 'Обновление категорий завершено',
        'error' => 'Ошибка при обновлении категорий'
    ],
    'updateProducts' => [
        'function' => 'fn_update_products',
        'success' => 'Обновление товаров завершено',
        'error' => 'Ошибка при обновлении товаров'
    ],
    'updatePrices' => [
        'function' => 'fn_update_prices',
        'success' => 'Обновление цен завершено',
        'error' => 'Ошибка при обновлении цен'
    ],
    'updateAmounts' => [
        'function' => 'fn_update_amounts',
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

        if ( $method['function']() ) {
            fn_set_notification('N', '', $method['success']);
        } else {
            fn_set_notification('E', '', $method['error']);
        }
        $suffix = '.manage';
        return array(CONTROLLER_STATUS_OK, 'price_parser' . $suffix);
    }

    Registry::set('navigation.tabs.price_parser', array (
        'title' => __('price_parser'),
        'js' => true
    ));

    Registry::get('view')->assign('price_parser', array());
}