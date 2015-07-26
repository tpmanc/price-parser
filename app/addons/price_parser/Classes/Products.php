<?php
/**
 * @author tpmanc <tpxtrime@mail.ru>
 */

namespace Classes;

use Classes\Image;

/**
 * Class to work with products
 * @package Classes
 */
class Products
{

    /**
     * Delete all products records in database
     *
     * @return boolean
     */
    public static function clearProducts()
    {
        $res1 = mysql_query("TRUNCATE TABLE `cscart_product_descriptions`");
        $res2 = mysql_query("TRUNCATE TABLE `cscart_product_prices`");
        $res3 = mysql_query("TRUNCATE TABLE `cscart_products`");
        $res4 = mysql_query("TRUNCATE TABLE `cscart_products_categories`");
        if ($res1 === false && $res2 === false && $res3 === false && $res4 === false) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Insert all products from input array to database
     * @param array $products Array with products info
     * @return boolean
     */
    public static function insertProducts(array $products)
    {
        /*
         * cscart_product_descriptions - ($id, "ru", "$title", "", "", "", "", "", "", "", "", "")
         * cscart_product_features_values - непонятно, не заполнял
         * cscart_product_options - непонятно, не заполнял
         * cscart_product_prices - ($id, $price, 0, 1, 0)
         * cscart_products - ($id, $art, "P", "A", 1, 0 , $amount, $weight|0, $length|0, $width|0, $height|0, 0, 0, time(), time(), 0, "N", "N", "N", "B", "N", "N", "R", "Y", "N", "N", "Y", 10, 0, "N", "", 0, 0, 0, 0, 10, "N", 0, "P", "F", "default", $shipping_params???, "", "", "", "N", "N", "Y", 0, "Y", 0, 0, "", "", "", "", "", "", "")
         * cscart_products_categories - ($productId, $categoryId, "M", 0)
         */
        $inStr1 = 'REPLACE INTO cscart_product_descriptions(product_id, lang_code, product, shortname, short_description, full_description, meta_keywords, meta_description, search_words, page_title, age_warning_message, promo_text) VALUES';
        $inStr2 = 'REPLACE INTO cscart_product_prices(product_id, price, percentage_discount, lower_limit, usergroup_id) VALUES';
        $inStr3 = 'REPLACE INTO cscart_products(product_id, product_code, product_type, status, company_id, list_price,
                    amount, weight, length, width, height, shipping_freight, low_avail_limit, timestamp, updated_timestamp, 
                    usergroup_ids, is_edp, edp_shipping, unlimited_download, tracking, free_shipping, feature_comparison, 
                    zero_price_action, is_pbp, is_op, is_oper, is_returnable, return_period, avail_since, out_of_stock_actions, 
                    localization, min_qty, max_qty, qty_step, list_qty_count, tax_ids, age_verification, age_limit, options_type, 
                    exceptions_type, details_layout, shipping_params, facebook_obj_type, yml_brand, yml_origin_country, yml_store, 
                    yml_pickup, yml_delivery, yml_cost, yml_export_yes, yml_bid, yml_cbid, yml_model, yml_sales_notes, yml_type_prefix, 
                    yml_market_category, yml_manufacturer_warranty, yml_seller_warranty, buy_now_url) VALUES';
        $inStr4 = 'REPLACE INTO cscart_products_categories(product_id, category_id, link_type, position) VALUES';
        $inArr1 = [];
        $inArr2 = [];
        $inArr3 = [];
        $inArr4 = [];
        $position = 0;
        $artOffset = 26493;
        $shipping_params = mysql_real_escape_string('a:5:{s:16:"min_items_in_box";i:0;s:16:"max_items_in_box";i:0;s:10:"box_length";i:0;s:9:"box_width";i:0;s:10:"box_height";i:0;}');
        $num = 0;
        foreach ($products as $p) {
            if(count($p) > 0) {
                $num++;
                $position += 10;
                $inArr1[] = '(' . $p['id'] . ', "ru", "' . mysql_real_escape_string($p['title']) . '", "", "", "", "", "", "", "", "", "")';
                $inArr2[] = '(' . $p['id'] . ', ' . $p['price'] . ', 0, 1, 0)';
                $inArr3[] = '(' . $p['id'] . ', "' . ((int)$p['art'] + $artOffset) . '", "P", "A", 1, 0 , ' .$p['count']. ', ' . $p['weight'] . ', ' . $p['length'] . ', ' . $p['width'] . ', ' . $p['height'] . ', 0, 0, ' . time() . ', ' . time() . ', 0, "N", "N", "N", "B", "N", "N", "R", "Y", "N", "N", "Y", 10, 0, "N", "", 0, 0, 0, 0, 10, "N", 0, "P", "F", "default", "' . $shipping_params . '", "", "", "", "N", "N", "Y", 0, "Y", 0, 0, "", "", "", "", "", "", "")';
                $inArr4[] = '('.$p['id'].', '.$p['categoryId'].', "M", 0)';
                if ($num == 2000) {
                    $num = 0;
                    mysql_query($inStr1 . implode(',', $inArr1)) or die('1. '.mysql_error());
                    mysql_query($inStr2 . implode(',', $inArr2)) or die('2. '.mysql_error());
                    mysql_query($inStr3 . implode(',', $inArr3)) or die('3. '.mysql_error());
                    mysql_query($inStr4 . implode(',', $inArr4)) or die('4. '.mysql_error());
                    $inArr1 = [];
                    $inArr2 = [];
                    $inArr3 = [];
                    $inArr4 = [];
                }
            }
        }
        $inStr1 = $inStr1 . implode(',', $inArr1);
        $inStr2 = $inStr2 . implode(',', $inArr2);
        $inStr3 = $inStr3 . implode(',', $inArr3);
        $inStr4 = $inStr4 . implode(',', $inArr4);

        $res1 = mysql_query($inStr1) or die('1. '.mysql_error());
        $res2 = mysql_query($inStr2) or die('2. '.mysql_error());
        $res3 = mysql_query($inStr3) or die('3. '.mysql_error());
        $res4 = mysql_query($inStr4) or die('4. '.mysql_error());

        return $res1 * $res2 * $res3 * $res4;
    }

    /**
     * Update products prices from input array
     * @param array $products Array with products prices
     * @return boolean
     */
    public static function updatePrices(array $products)
    {
        $res = true;
        foreach ($products as $p) {
            if (isset($p['id']) && isset($p['price'])) {
                $res = mysql_query('UPDATE cscart_product_prices
                                    SET price='. mysql_real_escape_string($p['price']) .'
                                    WHERE product_id = '.mysql_real_escape_string($p['id']));
            }
        }

        return ($res === false) ? false : true;
    }

    /**
     * Update products amount from input array
     * @param array $products Array with products amounts
     * @return boolean
     */
    public static function updateAmounts(array $products)
    {
        $res = true;
        foreach ($products as $p) {
            if (isset($p['count']) && isset($p['id'])) {
                $res = mysql_query('UPDATE cscart_products
                                    SET amount='. mysql_real_escape_string($p['count']) .'
                                    WHERE product_id = '.mysql_real_escape_string($p['id']));
            }
        }

        return ($res === false) ? false : true;
    }

    /**
     * Update products
     * Delete products that are not in the price list, insert new products
     * @param array $products Array with products amounts
     * @param array $images Array with images for products
     * @return bool
     */
    public static function updateProducts(array $products, array $images)
    {
        $dbProductsId = []; // storage for id off all products in database
        $inputProductsId = []; // storage for id off all products in input array
        $arrForInsert = [];
        $arrIdForInsert = [];
        $imagesArr = []; // images that we need to download for new products

        // check for insert products
        foreach ($products as $p) {
            if (!empty($p)) {
                $q = mysql_query('SELECT product_id FROM cscart_products WHERE product_id=' . $p['id']);
                if ($q !== false && $q !== null) {
                    $r = mysql_result($q, 0);
                    if ($r == null) {
                        $arrForInsert[] = $p;
                        $arrIdForInsert[] = $p['id'];
                    }
                    $inputProductsId[] = (int)$p['id'];
                } else {
                    $inputProductsId[] = (int)$p['id'];
                }
            }
        }
        $res1 = true;
        if (!empty($arrForInsert)) {
            $res1 = self::insertProducts($arrForInsert);
            foreach ($arrIdForInsert as $i) {
                if (isset($images[$i])) {
                    $imagesArr[] = $images[$i];
                }
            }
            if (!empty($imagesArr)) {
                Image::downloadAndLink($imagesArr);
            }
        }

        // check for delete
        $q = mysql_query('SELECT product_id FROM cscart_products');
        while ($r = mysql_fetch_array($q)) {
            $dbProductsId[] = (int)$r['product_id'];
        }
        $arrForDelete = array_diff($dbProductsId, $inputProductsId);
        $res2 = true;
        $res3 = true;
        $res4 = true;
        $res5 = true;
        $res6 = true;
        if (!empty($arrForDelete)) {
            $res2 = mysql_query('DELETE FROM cscart_product_descriptions WHERE product_id in ('.implode(',', $arrForDelete).')');
            $res3 = mysql_query('DELETE FROM cscart_product_prices WHERE product_id in ('.implode(',', $arrForDelete).')');
            $res4 = mysql_query('DELETE FROM cscart_products WHERE product_id in ('.implode(',', $arrForDelete).')');
            $res5 = mysql_query('DELETE FROM cscart_products_categories WHERE product_id in ('.implode(',', $arrForDelete).')');
            $imgIdArr = [];
            $q = mysql_query('SELECT detailed_id FROM cscart_images_links WHERE object_type="product" AND object_id in ('.implode(',', $arrForDelete).')');
            while ($r = mysql_fetch_array($q)) {
                $imgIdArr[] = $r['detailed_id'];
            }
            if (!empty($imgIdArr)) {
                $res6 = Image::deleteImagesById($imgIdArr);
            }
        }

        return $res1 * $res2 * $res3 * $res4 * $res5 * $res6;
    }
}