<?php
/**
 * @author tpmanc <tpxtrime@mail.ru>
 */

namespace Classes;

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
                        $pictureUrl = '';
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
                                if($reader->name == 'priceE'){
                                    $price = $currency * (float)$reader->readString();
                                }
                                if($reader->name == 'uid'){
                                    $art = $reader->readString();
                                }
                                if($reader->name == 'picture'){
                                    $pictureUrl = $reader->readString();
                                    if ($pictureUrl !== '') {
                                        $images[] = [
                                            'productId' => $productId,
                                            'pictureUrl' => $pictureUrl . '&id=' . $productId,
                                        ];
                                    }
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
     * @return array Products arrays
     */
    public static function parseProductsPrices($file)
    {
        $products = [];

        $reader = new \XMLReader();
        $reader->open($file);
        while ($reader->read()) {
            switch ($reader->nodeType) {
                case (\XMLReader::ELEMENT):
                    // parsing <offer> element
                    $product = [];
                    if ($reader->localName == 'offer') {
                        $product = [];
                        $price = '';
                        $productId = $reader->getAttribute("id");

                        // get product properties
                        while ($reader->read()){
                            if ($reader->nodeType == \XMLReader::ELEMENT) {
                                if($reader->name == 'priceE'){
                                    $price = $reader->readString();
                                }
                            }
                            if ($reader->nodeType == \XMLReader::END_ELEMENT && $reader->localName == 'offer') {
                                $product = [
                                    'id' => $productId,
                                    'price' => $price,
                                ];
                                break;
                            }
                        }
                    }
                    $products[] = $product;
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