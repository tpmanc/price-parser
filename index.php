<?php

require 'ModuleController.php';

mysql_connect('localhost', 'root', '');
mysql_select_db('sc');
mysql_query("SET NAMES 'utf8';");
mysql_query("SET CHARACTER SET 'utf8';");
mysql_query("SET SESSION collation_connection = 'utf8_general_ci';");

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Archives downloading
echo 'Скачиваем архивы <br />';
\ModuleController::downloadPrices();
echo 'ок! <br />';

// Archives extracting
echo 'Распаковываем архивы <br />';
if( \ModuleController::extract() ){
    echo 'Распаковка архивов завершена <br />';
}else{
    die('Ошибка при распаковке архивов <br />');
}

// Clear database
\ModuleController::clearDatabase();

// Fill empty database
\ModuleController::fillEmptyDatabase();


exit();

// сохранение картинок
//$s = 'http://www.netlab.ru/ISAPI/TestISAPI.dll?74614&amp;0';
//$img = 'flower.jpg';
//file_put_contents($img, file_get_contents($s));
