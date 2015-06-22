<?php
/**
 * @author tpmanc <tpxtrime@mail.ru>
 */

require 'Classes/FileParser.php';
require 'Classes/FileHelper.php';
require 'Classes/Categories.php';
require 'Classes/Products.php';
require 'Classes/Image.php';
require 'Classes/Properties.php';

use \Classes\FileHelper;
use \Classes\FileParser;
use \Classes\Categories;
use \Classes\Products;
use \Classes\Image;
use \Classes\Properties;

/**
 * Class ModuleController - provides methods to work with prices
 */
class ModuleController
{

    /**
     * @var string Url for downloading price
     */
    private static $priceUrl = 'http://www.netlab.ru/products/priceXML.zip';

    /**
     * @var string Url for downloading properties
     */
    private static $propertiesUrl = 'http://www.netlab.ru/products/GoodsProperties.zip';

    /**
     * @var string Path to download price xml
     */
    private static $priceZipPath = './temp/price.zip';

    /**
     * @var string Path to download properties xml
     */
    private static $propertiesZipPath = './temp/properties.zip';

    /**
     * @var string Path to unzipped price
     */
    private static $unzippedPrice = './temp/Price.xml';

    /**
     * @var string Path to unzipped properties
     */
    private static $unzippedProperties = './temp/GoodsProperties.xml';

    /**
     * Downloading price and properties
     * @return void
     */
    public static function downloadPrices()
    {
        FileHelper::download(self::$priceUrl, self::$priceZipPath);
        FileHelper::download(self::$propertiesUrl, self::$propertiesZipPath);
    }

    /**
     * Extract zip archives
     * @return boolean Boolean result of extracting
     */
    public static function extract()
    {
        $price = FileHelper::unzip(self::$priceZipPath, './temp/');
        $prop = FileHelper::unzip(self::$propertiesZipPath, './temp/');

        return $price * $prop;
    }

    /**
     * Insert to empty database categories and products
     * @return void
     */
    public static function fillEmptyDatabase()
    {
        $arr = FileParser::parseCatsAndProducts(self::$unzippedPrice);
        $categories = $arr['categories'];
        $products = $arr['products'];

        // Categories saving
        Categories::clearCategories();

        $res = Categories::insertCategories($categories);
        if( $res === true ){
            echo 'Categories insert complete<br />';
        }else{
            echo 'Categories insert error: <br />', $res;
        }

        $res = Products::insertProducts($products);
        if( $res === true ){
            echo 'Products insert complete<br />';
        }else{
            echo 'Products insert error: <br />', $res;
        }

        // TODO: property parsing
        $arr = FileParser::parseProperties(self::$unzippedProperties);
        $properties = $arr['properties'];
        $products = $arr['products'];
        $res = Properties::insertProperties($properties);
        if( $res === true ){
            echo 'Properties insert complete<br />';
        }else{
            echo 'Properties insert error: <br />', $res;
        }
        $res = Properties::addPropertyToProduct($products);
        if( $res === true ){
            echo 'Properties links insert complete<br />';
        }else{
            echo 'Properties links insert error: <br />', $res;
        }
    }

    /**
     * Update products prices
     * @return void
     */
    public static function updateProductsPrices()
    {
        $products = FileParser::parseProductsPrices(self::$unzippedPrice);
        Products::updatePrices($products);
    }

    /**
     * Delete all categories and product from database
     * @return void
     */
    public static function clearDatabase()
    {
        Categories::clearCategories();
        Products::clearProducts();
        Properties::clearProperties();
        // Image::clearImages();
    }
}