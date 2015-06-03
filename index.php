<?php
mysql_connect('localhost', 'root', '');
mysql_select_db('sc');

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

include('vendor/autoload.php');


// echo 'Скачиваем архивы <br />';
// file_put_contents("price.zip", fopen("http://www.netlab.ru/products/priceXML.zip", 'r'));
// file_put_contents("properties.zip", fopen("http://www.netlab.ru/products/GoodsProperties.zip", 'r'));
// echo 'ок! <hr />';


// echo 'Распаковываем <br />';
// $zip = new ZipArchive;
// if ($zip->open('./priceXML.zip') === true) {
//     $zip->extractTo('./');
//     $zip->close();
    


// } else {
//     echo 'ошибка';
// }

mysql_query('truncate table `cscart_category_descriptions`');

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
                $categories[$categoryId] = ['id' => $categoryId, 'parentId' => $parentId, 'title' => $reader->readString()];
            }
/*
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
*/
    }
}

/*
 * Таблица cscart_categories
 * id_path - путь из id до категории, например, 166/167/165
 * level - уровень вложенности
 * status - А-включено, D-выключено, -скрыто
 */
$inStr = "INSERT INTO cscart_category_descriptions(category_id, lang_code, category) VALUES";
$inArr = [];
foreach($categories as $c){
    $inArr[] = '('.$c['id'].', "ru", "'.$c['title'].'"")';
}

var_dump($categories);
exit();