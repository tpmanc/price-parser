<?php
mysql_connect('localhost', 'root', '');
mysql_select_db('sc');

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

//include('vendor/autoload.php');

//use Classes\FileHelper;

// echo 'Скачиваем архивы <br />';
//FileHelper::download("http://www.netlab.ru/products/priceXML.zip", "price.zip");
//FileHelper::download("http://www.netlab.ru/products/GoodsProperties.zip", "properties.zip");
// echo 'ок! <hr />';


// echo 'Распаковываем <br />';
// $zip = new ZipArchive;
// if ($zip->open('./priceXML.zip') === true) {
//     $zip->extractTo('./');
//     $zip->close();
// } else {
//     echo 'ошибка';
// }

mysql_query("SET NAMES 'utf8';");
mysql_query("SET CHARACTER SET 'utf8';");
mysql_query("SET SESSION collation_connection = 'utf8_general_ci';");

$categories = [];
$products = [];

$reader = new XMLReader();
$reader->open('Price.xml');
$item = array();
while ($reader->read()) {
    switch ($reader->nodeType) {
        case (XMLReader::ELEMENT):
        	// parsing <category> element
            if ($reader->localName == 'category') {
                $categoryId = $reader->getAttribute("id");
                $parentId = $reader->getAttribute("parentId");
                $title = $reader->readString();
                $categories[$categoryId] = [
                    'id' => $categoryId,
                    'parentId' => $parentId,
                    'title' => $title,
                ];
            }
            // parsing <offer> element
            $product = [];
            if ($reader->localName == 'offer') {
                $product = [];
                $title = '';
                $warranty = '';
                $weight = '';
                $price = '';
                $art = '';
                $count = 0;
                $weight = 0;
                $length = 0;
                $width = 0;
                $height = 0;
                $categoryId = 0;
                $productId = $reader->getAttribute("id");

                // get product properties
                while ($reader->read()){
                    if ($reader->nodeType == XMLReader::ELEMENT) {
                        if($reader->name == 'name'){
                            $title = $reader->readString();
                        }
                        if($reader->name == 'categoryId'){
                            $categoryId = $reader->readString();
                        }
                        if($reader->name == 'warranty'){
                            $warranty = $reader->readString();
                        }
                        if($reader->name == 'priceE'){
                            $price = $reader->readString();
                        }
                        if($reader->name == 'uid'){
                            $art = $reader->readString();
                        }
                        if($reader->name == 'count'){
                            if( is_numeric($reader->readString()) ){
                                $count = $reader->readString();
                            }
                        }
                        if($reader->name == 'weight'){
                            if( is_numeric($reader->readString()) ){
                                $weight = $reader->readString();
                            }
                        }
                        if($reader->name == 'length'){
                            if( is_numeric($reader->readString()) ){
                                $length = $reader->readString();
                            }
                        }
                        if($reader->name == 'width'){
                            if( is_numeric($reader->readString()) ){
                                $width = $reader->readString();
                            }
                        }
                        if($reader->name == 'height'){
                            if( is_numeric($reader->readString()) ){
                                $height = $reader->readString();
                            }
                        }

                    }
                    if ($reader->nodeType == XMLReader::END_ELEMENT && $reader->localName == 'offer') {
                        $product = [
                            'id' => $productId,
                            'categoryId' => $categoryId,
                            'title' => $title,
                            'art' => $art,
                            'warranty' => $warranty,
                            'price' => $price,
                            'weight' => $weight,
                            'height' => $height,
                            'width' => $width,
                            'length' => $length,
                            'count' => $count,
                        ];
                        break;
                    }
                }
            }
            $products[] = $product;
    }
}

// --------------- categories saving -------------------
/**
 * Таблица cscart_categories
 * id_path - путь из id до категории, например, 166/167/165
 * level - уровень вложенности 1,2,3...
 * status - А-включено, D-выключено, -скрыто
 *
 * cscart_static_data - ($param_id, "", "", !, "", 2, "ty-menu-item__products", "A", "A", $position, $parent_id, $id_path, "", 1)
 * cscart_static_data_descriptions - ($param_id, "ru", "$title")
 */
if( false ) {
    mysql_query("TRUNCATE TABLE `cscart_category_descriptions`");
    mysql_query("TRUNCATE TABLE `cscart_categories`");
    mysql_query("DELETE FROM cscart_static_data WHERE param_5=2"); // очищаем верхнее меню на фронте

    $inStr1 = "INSERT INTO cscart_category_descriptions(category_id, lang_code, category, description, meta_keywords, meta_description, page_title, age_warning_message) VALUES";
    $inStr2 = "INSERT INTO cscart_categories(category_id, parent_id, id_path, level, company_id, usergroup_ids, status, product_count, position, timestamp, is_op, localization, age_verification, age_limit, parent_age_verification, parent_age_limit, selected_views, default_view, product_details_view, product_columns, yml_market_category, yml_disable_cat) VALUES";
    $inArr1 = [];
    $inArr2 = [];
    $position = 0;
    foreach ($categories as $c) {
        $level = 1; // level counter
        $idPathArr = [
            $c['id']
        ];
        $parentId = $c['parentId'];
        while ($parentId !== null) {
            $idPathArr[] = $categories[$parentId]['id'];
            $level++;
            $parentId = $categories[$parentId]['parentId'];
        }
        $idPathArr = array_reverse($idPathArr);
        $par = $c['parentId'];
        if ($par == null) {
            $par = 0;
        }
        $position += 10;
        $inArr1[] = '(' . $c['id'] . ', "ru", "' . mysql_real_escape_string($c['title']) . '", "", "", "", "", "")';
        $inArr2[] = '(' . $c['id'] . ',' . $par . ',"' . implode('/', $idPathArr) . '",' . $level . ',1,0,"A",0,' . $position . ',' . time() . ',"N","","N",0,"N",0,"","","default",0,"","N")';
    }
    $inStr1 = $inStr1 . implode(',', $inArr1);
    $inStr2 = $inStr2 . implode(',', $inArr2);

    mysql_query($inStr1);
    mysql_query($inStr2);
}
// --------------- end categories saving -------------------



// --------------- products saving -------------------
/**
 * cscart_product_descriptions - ($id, "ru", "$title", "", "", "", "", "", "", "", "", "")
 * cscart_product_features_values - непонятно, не заполнял
 * cscart_product_options - непонятно, не заполнял
 * cscart_product_prices - ($id, $price, 0, 1, 0)
 * cscart_products - ($id, $art, "P", "A", 1, 0 , $amount, $weight|0, $length|0, $width|0, $height|0, 0, 0, time(), time(), 0, "N", "N", "N", "B", "N", "N", "R", "Y", "N", "N", "Y", 10, 0, "N", "", 0, 0, 0, 0, 10, "N", 0, "P", "F", "default", $shipping_params???, "", "", "", "N", "N", "Y", 0, "Y", 0, 0, "", "", "", "", "", "", "")
 * cscart_products_categories - ($productId, $categoryId, "M", 0)
 */

if( true ){
    mysql_query("TRUNCATE TABLE `cscart_product_descriptions`");
    mysql_query("TRUNCATE TABLE `cscart_product_prices`");
    mysql_query("TRUNCATE TABLE `cscart_products`");
    mysql_query("TRUNCATE TABLE `cscart_products_categories`");

    $inStr1 = "INSERT INTO cscart_product_descriptions(product_id, lang_code, product, shortname, short_description, full_description, meta_keywords, meta_description, search_words, page_title, age_warning_message, promo_text) VALUES";
    $inStr2 = "INSERT INTO cscart_product_prices(product_id, price, percentage_discount, lower_limit, usergroup_id) VALUES";
    $inStr3 = "INSERT INTO cscart_products(product_id, product_code, product_type, status, company_id, list_price, amount, weight, length, width, height, shipping_freight, low_avail_limit, timestamp, updated_timestamp, usergroup_ids, is_edp, edp_shipping, unlimited_download, tracking, free_shipping, feature_comparison, zero_price_action, is_pbp, is_op, is_oper, is_returnable, return_period, avail_since, out_of_stock_actions, localization, min_qty, max_qty, qty_step, list_qty_count, tax_ids, age_verification, age_limit, options_type, exceptions_type, details_layout, shipping_params, facebook_obj_type, yml_brand, yml_origin_country, yml_store, yml_pickup, yml_delivery, yml_cost, yml_export_yes, yml_bid, yml_cbid, yml_model, yml_sales_notes, yml_type_prefix, yml_market_category, yml_manufacturer_warranty, yml_seller_warranty, buy_now_url) VALUES";
    $inStr4 = "INSERT INTO cscart_products_categories(product_id, category_id, link_type, position) VALUES";
    $inArr1 = [];
    $inArr2 = [];
    $inArr3 = [];
    $inArr4 = [];
    $position = 0;
    $artOffset = 26493;
    $shipping_params = mysql_real_escape_string('a:5:{s:16:"min_items_in_box";i:0;s:16:"max_items_in_box";i:0;s:10:"box_length";i:0;s:9:"box_width";i:0;s:10:"box_height";i:0;}');
    foreach ($products as $p) {
        if(count($p) > 0) {
            $position += 10;
            $inArr1[] = '(' . $p['id'] . ', "ru", "' . mysql_real_escape_string($p['title']) . '", "", "", "", "", "", "", "", "", "")';
            $inArr2[] = '(' . $p['id'] . ', ' . $p['price'] . ', 0, 1, 0)';
            $inArr3[] = '(' . $p['id'] . ', "' . ((int)$p['art'] + $artOffset) . '", "P", "A", 1, 0 , ' .$p['count']. ', ' . $p['weight'] . ', ' . $p['length'] . ', ' . $p['width'] . ', ' . $p['height'] . ', 0, 0, ' . time() . ', ' . time() . ', 0, "N", "N", "N", "B", "N", "N", "R", "Y", "N", "N", "Y", 10, 0, "N", "", 0, 0, 0, 0, 10, "N", 0, "P", "F", "default", "' . $shipping_params . '", "", "", "", "N", "N", "Y", 0, "Y", 0, 0, "", "", "", "", "", "", "")';
            $inArr4[] = '('.$p['id'].', '.$p['categoryId'].', "M", 0)';
        }
    }
    $inStr1 = $inStr1 . implode(',', $inArr1);
    $inStr2 = $inStr2 . implode(',', $inArr2);
    $inStr3 = $inStr3 . implode(',', $inArr3);
    $inStr4 = $inStr4 . implode(',', $inArr4);

    mysql_query($inStr1) or die(mysql_error());
    mysql_query($inStr2) or die(mysql_error());
    mysql_query($inStr3) or die(mysql_error());
    mysql_query($inStr4);
}

// сохранение картинок
//$s = 'http://www.netlab.ru/ISAPI/TestISAPI.dll?74614&amp;0';
//$img = 'flower.jpg';
//file_put_contents($img, file_get_contents($s));
