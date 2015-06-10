<?php
/**
 * @author tpmanc <tpxtrime@mail.ru>
 */

namespace Classes;

/**
 * Class to work with products
 * @package Classes
 */
class Products {

    /**
     * Delete all products records in database
     *
     * @return void
     */
    public static function clearProducts()
    {
        mysql_query("TRUNCATE TABLE `cscart_product_descriptions`");
        mysql_query("TRUNCATE TABLE `cscart_product_prices`");
        mysql_query("TRUNCATE TABLE `cscart_products`");
        mysql_query("TRUNCATE TABLE `cscart_products_categories`");
    }

    /**
     * Insert all products from input array to database
     * @param array $products Array with prdicts info
     * @return mixed Return true or error
     */
    public static function insertProducts($products)
    {
        /*
         * cscart_product_descriptions - ($id, "ru", "$title", "", "", "", "", "", "", "", "", "")
         * cscart_product_features_values - непонятно, не заполнял
         * cscart_product_options - непонятно, не заполнял
         * cscart_product_prices - ($id, $price, 0, 1, 0)
         * cscart_products - ($id, $art, "P", "A", 1, 0 , $amount, $weight|0, $length|0, $width|0, $height|0, 0, 0, time(), time(), 0, "N", "N", "N", "B", "N", "N", "R", "Y", "N", "N", "Y", 10, 0, "N", "", 0, 0, 0, 0, 10, "N", 0, "P", "F", "default", $shipping_params???, "", "", "", "N", "N", "Y", 0, "Y", 0, 0, "", "", "", "", "", "", "")
         * cscart_products_categories - ($productId, $categoryId, "M", 0)
         */
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

            // TODO: image downloading
        }
        $inStr1 = $inStr1 . implode(',', $inArr1);
        $inStr2 = $inStr2 . implode(',', $inArr2);
        $inStr3 = $inStr3 . implode(',', $inArr3);
        $inStr4 = $inStr4 . implode(',', $inArr4);

        mysql_query($inStr1) or die(mysql_error());
        mysql_query($inStr2) or die(mysql_error());
        mysql_query($inStr3) or die(mysql_error());
        mysql_query($inStr4) or die(mysql_error());

        return true;
    }
}