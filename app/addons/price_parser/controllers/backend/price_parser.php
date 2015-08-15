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

    $dbHost = Registry::get('config.db_host');
    $dbUser = Registry::get('config.db_user');
    $dbPass = Registry::get('config.db_password');
    $dbName = Registry::get('config.db_name');
    $mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
    if (mysqli_connect_errno()) {
        printf("Подключение к серверу MySQL невозможно. Код ошибки: %s\n", mysqli_connect_error());
        die();
    }

    $categories = array();
    if ($result = $mysqli->query('SELECT cscart_category_descriptions.category_id, cscart_category_descriptions.category, cscart_addon_margins.margin 
                                    FROM cscart_category_descriptions
                                    LEFT JOIN cscart_addon_margins 
                                    ON cscart_addon_margins.category_id = cscart_category_descriptions.category_id
                                    ')) { 
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
        $result->close();
    }
    $mysqli->close();

    Registry::get('view')->assign('categories', $categories);
}

if ($mode == 'update') {
    $categories = $_REQUEST['cm'];
    $sql = 'INSERT INTO cscart_addon_margins(category_id, margin) VALUES';
    $inArr = [];
    foreach ($categories as $id => $m) {
        if (is_numeric($m) && $m != 0) {
            $inArr[] = '('.$id.', '.$m.')';
        }
    }

    $dbHost = Registry::get('config.db_host');
    $dbUser = Registry::get('config.db_user');
    $dbPass = Registry::get('config.db_password');
    $dbName = Registry::get('config.db_name');
    $mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
    if (mysqli_connect_errno()) {
        printf("Подключение к серверу MySQL невозможно. Код ошибки: %s\n", mysqli_connect_error());
        die();
    }
    $mysqli->query('TRUNCATE TABLE cscart_addon_margins');
    $mysqli->query($sql . implode(',', $inArr));

    return array(CONTROLLER_STATUS_OK, 'price_parser.manage');
}