<?php
/**
 * @author tpmanc <tpxtrime@mail.ru>
 */

namespace Classes;

use Tygh\Registry;

/**
 * Class for parsing files
 * @package Classes
 */
class FileParser
{
    /**
     * Parse xml with products
     *
     * @param string $file Path to file
     * @return array Categories and products arrays
     */
    public static function parseCatsAndProducts($file)
    {
        $categories = [];
        $products = [];
        $images = [];
        $currency = 1;
        $priceField = Registry::get('addons.price_parser.productPriceField');
        $imgCode = Registry::get('addons.price_parser.imageCode');

        $reader = new \XMLReader();
        $reader->open($file);
        while ($reader->read()) {
            switch ($reader->nodeType) {
                case (\XMLReader::ELEMENT):
                    // parsing <currency> element
                    if ($reader->localName == 'currency') {
                        if ($reader->getAttribute('id') === 'USD') {
                            $currency = (float)$reader->getAttribute('rate');
                        }
                    }
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
                            if ($reader->nodeType == \XMLReader::ELEMENT) {
                                if($reader->name == 'name'){
                                    $title = $reader->readString();
                                }
                                if($reader->name == 'categoryId'){
                                    $categoryId = $reader->readString();
                                }
                                if($reader->name == 'warranty'){
                                    $warranty = $reader->readString();
                                }
                                if($reader->name == $priceField){
                                    $price = $currency * (float)$reader->readString();
                                }
                                if($reader->name == 'uid'){
                                    $art = $reader->readString();
                                }
                                if($reader->name == 'picture'){
                                    $pictureUrl = str_replace('&0', '&' . $imgCode, $reader->readString());
                                    if ($pictureUrl !== '') {
                                        $images[$productId] = [
                                            'productId' => $productId,
                                            'pictureUrl' => $pictureUrl . '#' . $productId,
                                        ];
                                    }
                                }
                                if($reader->name == 'count'){
                                    $count = $reader->readString();
                                    if ($count === '***') {
                                        $count = 30;
                                    } elseif ($count === '**') {
                                        $count = 20;
                                    } elseif ($count === '*') {
                                        $count = 10;
                                    } else {
                                        $count = 0;
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
                            if ($reader->nodeType == \XMLReader::END_ELEMENT && $reader->localName == 'offer') {
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

        return [
            'categories' => $categories,
            'products' => $products,
            'images' => $images,
        ];
    }

    /**
     * Parse xml with products
     *
     * @param string $file Path to file
     * @param string|boolean $priceField Product price field name or false
     * @return array Products arrays
     */
    public static function parseProductsPricesAndAmount($file, $priceField = false)
    {
        $products = [];
        $currency = 1;
        if ($priceField === false) {
            $priceField = Registry::get('addons.price_parser.productPriceField');
        }

        $reader = new \XMLReader();
        $reader->open($file);
        while ($reader->read()) {
            $count = 0;
            switch ($reader->nodeType) {
                case (\XMLReader::ELEMENT):
                    // parsing <currency> element
                    if ($reader->localName == 'currency') {
                        if ($reader->getAttribute('id') === 'USD') {
                            $currency = (float)$reader->getAttribute('rate');
                        }
                    }
                    // parsing <offer> element
                    $product = [];
                    if ($reader->localName == 'offer') {
                        $product = [];
                        $price = '';
                        $productId = $reader->getAttribute("id");

                        // get product properties
                        while ($reader->read()){
                            if ($reader->nodeType == \XMLReader::ELEMENT) {
                                if($reader->name == $priceField){
                                    $price = $currency * (float)$reader->readString();
                                }
                                if($reader->name == 'categoryId'){
                                    $categoryId = $reader->readString();
                                }
                                if($reader->name == 'count'){
                                    $count = $reader->readString();
                                    if ($count === '***') {
                                        $count = 30;
                                    } elseif ($count === '**') {
                                        $count = 20;
                                    } elseif ($count === '*') {
                                        $count = 10;
                                    } else {
                                        $count = 0;
                                    }
                                }
                            }
                            if ($reader->nodeType == \XMLReader::END_ELEMENT && $reader->localName == 'offer') {
                                $product = [
                                    'id' => $productId,
                                    'categoryId' => $categoryId,
                                    'price' => $price,
                                    'count' => $count,
                                ];
                                break;
                            }
                        }
                    }
                    if (!empty($product)) { 
                        $products[] = $product;
                    }
            }
        }

        return $products;
    }

    /**
     * Parse xml with products
     *
     * @param string $file Path to file
     * @return array Categories and products arrays
     */
    public static function parseProperties($file)
    {
        $properties = [];
        $products = [];

        $reader = new \XMLReader();
        $reader->open($file);
        while ($reader->read()) {
            switch ($reader->nodeType) {
                case (\XMLReader::ELEMENT):
                    // parsing <property> element
                    if ($reader->localName == 'property') {
                        $propertyTitle = $reader->readString();
                        $propertyId = $reader->getAttribute("id");
                        $properties[] = [
                            'propertyId' => $propertyId,
                            'propertyTitle' => $propertyTitle,
                        ];
                    }
                    // parsing <item> element
                    if ($reader->localName == 'item') {
                        $productProperties = [];
                        $productId = $reader->getAttribute("id");

                        // get product properties
                        while ($reader->read()){
                            if ($reader->nodeType == \XMLReader::ELEMENT) {
                                $productProperties[] = [
                                    'propertyId' => $reader->localName,
                                    'propertyValue' => $reader->readString(),
                                ];
                            }
                            if ($reader->nodeType == \XMLReader::END_ELEMENT && $reader->localName == 'item') {
                                break;
                            }
                        }
                        $products[] = [
                            'productId' => mb_strcut($productId, 1),
                            'productProperties' => $productProperties
                        ];
                    }
            }
        }

        return [
            'properties' => $properties,
            'products' => $products,
        ];
    }
}