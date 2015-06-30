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
     * @return boolean
     */
    public static function downloadPrices()
    {
        $res1 = FileHelper::download(self::$priceUrl, self::$priceZipPath);
        $res2 = FileHelper::download(self::$propertiesUrl, self::$propertiesZipPath);
        return $res1 * $res2;
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
     * @return boolean
     */
    public static function fillEmptyDatabase()
    {
        $arr = FileParser::parseCatsAndProducts(self::$unzippedPrice);
        $categories = $arr['categories'];
        $products = $arr['products'];
        $images = $arr['images'];

        // Categories saving
        $res1 = Categories::insertCategories($categories);

        // Products saving
        $res2 = Products::insertProducts($products);

        // Images saving
        $res3 = Image::downloadAndLink($images);

        // Properties parsing and saving
        $arr = FileParser::parseProperties(self::$unzippedProperties);
        $properties = $arr['properties'];
        $products = $arr['products'];
        $res4 = Properties::insertProperties($properties);

        $res5 = Properties::addPropertyToProduct($products);

        if ($res1 === false && $res2 === false && $res3 === false && $res4 === false && $res5 === false) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Update products prices
     * @return boolean
     */
    public static function updatePrices()
    {
        $products = FileParser::parseProductsPricesAndAmount(self::$unzippedPrice);
        return Products::updatePrices($products);
    }

    /**
     * Delete all categories and product from database
     * @return boolean
     */
    public static function clearDatabase()
    {
        $res1 = Categories::clearCategories();
        $res2 = Products::clearProducts();
        $res3 = Properties::clearProperties();
        $res4 = Image::clearImages();

        return $res1 * $res2 * $res3 * $res4;
    }

    /**
     * Update products amounts
     * @return boolean
     */
    public static function updateAmounts()
    {
        $products = FileParser::parseProductsPricesAndAmount(self::$unzippedPrice);
        return Products::updateAmounts($products);
    }
}