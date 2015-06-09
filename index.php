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
                $productId = $reader->getAttribute("id");

                // get product properties
                while ($reader->read()){
                    if ($reader->nodeType == XMLReader::ELEMENT) {
                        if($reader->name == 'name'){
                            $title = $reader->readString();
                        }
                        if($reader->name == 'warranty'){
                            $warranty = $reader->readString();
                        }
                        if($reader->name == 'weight'){
                            $weight = $reader->readString();
                        }
                        if($reader->name == 'price'){
                            $price = $reader->readString();
                        }

                    }
                    if ($reader->nodeType == XMLReader::END_ELEMENT && $reader->localName == 'offer')
                        break;
                }
                $product[$productId] = [
                    'id' => $productId,
                    'title' => $title,
                    'warranty' => $warranty,
                    'price' => $price,
                    'weight' => $weight,
                ];
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
 * cscart_product_features_values - непонятно
 * cscart_product_options - непонятно
 * cscart_product_prices - ($id, $price, 0, 1, 0)
 * cscart_products - ($id, $art, "P", "A", 1, 0 , $amount, $weight|0, $length|0, $width|0, $height|0, 0, 0, time(), time(), 0, "N", "N", "N", "B", "N", "N", "R", "Y", "N", "N", "Y", 10, 0, "N", "", 0, 0, 0, 0, 10, "N", 0, "P", "F", "default", $shipping_params???, "", "", "", "N", "N", "Y", 0, "Y", 0, 0, "", "", "", "", "", "", "")
 * cscart_products_categories - ($productId, $categoryId, "M", 0)
 */
