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
require 'Classes/RollingCurl.php';
require 'Classes/Request.php';

use \Classes\FileHelper;
use \Classes\FileParser;
use \Classes\Categories;
use \Classes\Products;
use \Classes\Image;
use \Classes\Properties;
use Tygh\Registry;

/**
 * Class ModuleController - provides methods to work with prices
 */
class ModuleController
{
    /**
     * @var string Path to download price xml
     */
    private static $priceZipPath = '/temp/price.zip';

    /**
     * @var string Path to download properties xml
     */
    private static $propertiesZipPath = '/temp/properties.zip';

    /**
     * @var string Path to unzipped price
     */
    private static $unzippedPrice = '/temp/Price.xml';

    /**
     * @var string Path to unzipped properties
     */
    private static $unzippedProperties = '/temp/GoodsProperties.xml';

    /**
     * Downloading price and properties
     * @param string $pathToAddon Path to addon folder
     * @return bool
     */
    public static function downloadPrices($pathToAddon)
    {
        $priceUrl = Registry::get('addons.price_parser.productsPriceUrl');
        $propertiesUrl = Registry::get('addons.price_parser.propertiesPriceUrl');

        $res1 = FileHelper::download($priceUrl, $pathToAddon . self::$priceZipPath);
        $res2 = FileHelper::download($propertiesUrl, $pathToAddon . self::$propertiesZipPath);
        return $res1 * $res2;
    }

    /**
     * Extract zip archives
     * @param string $pathToAddon Path to addon folder
     * @return boolean Boolean result of extracting
     */
    public static function extract($pathToAddon)
    {
        $price = FileHelper::unzip($pathToAddon . self::$priceZipPath, $pathToAddon . '/temp/');
        $prop = FileHelper::unzip($pathToAddon . self::$propertiesZipPath, $pathToAddon . '/temp/');

        return $price * $prop;
    }

    /**
     * Insert to empty database categories and products
     * @param string $pathToAddon Path to addon folder
     * @return boolean
     */
    public static function fillEmptyDatabase($pathToAddon)
    {
        $arr = FileParser::parseCatsAndProducts($pathToAddon . self::$unzippedPrice);
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
        $arr = FileParser::parseProperties($pathToAddon . self::$unzippedProperties);

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
     * @param string $pathToAddon Path to addon folder
     * @param string|boolean $priceField Product price field name or false
     * @return boolean
     */
    public static function updatePrices($pathToAddon, $priceField = false)
    {
        $products = FileParser::parseProductsPricesAndAmount($pathToAddon . self::$unzippedPrice, $priceField);
        return Products::updatePrices($products);
    }

    /**
     * Delete all categories, product, images, properties from database
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
     * @param string $pathToAddon Path to addon folder
     * @return boolean
     */
    public static function updateAmounts($pathToAddon)
    {
        $products = FileParser::parseProductsPricesAndAmount($pathToAddon . self::$unzippedPrice);
        return Products::updateAmounts($products);
    }

    /**
     * Update all categories
     *
     * Remove all categories from database and insert it from price list
     * @param string $pathToAddon Path to addon folder
     * @return boolean
     */
    public static function updateCategories($pathToAddon)
    {
        $categories = FileParser::parseCatsAndProducts($pathToAddon . self::$unzippedPrice)['categories'];
        $res2 = Categories::updateCategories($categories);

        return $res2;
    }

    /**
     * Update all products properties
     * @param string $pathToAddon Path to addon folder
     * @return boolean
     */
    public static function updateProducts($pathToAddon)
    {
        $arr = FileParser::parseCatsAndProducts($pathToAddon . self::$unzippedPrice);
        $res1 = Products::updateProducts($arr['products'], $arr['images']);
        
        // $arr = FileParser::parseProperties($pathToAddon . self::$unzippedProperties);
        // $res3 = Properties::insertProperties($arr['properties']);
        // $res4 = Properties::addPropertyToProduct($arr['products']);

        // return $res1 * $res2 * $res3 * $res4;
        return $res1;
    }

    public static function updateProperties($pathToAddon)
    {
        $res2 = Properties::clearProperties();
        $arr = FileParser::parseProperties($pathToAddon . self::$unzippedProperties);
        $res3 = Properties::insertProperties($arr['properties']);
        $res4 = Properties::addPropertyToProduct($arr['products']);

        return $res4;
    }
}