<?php
/**
 * @author tpmanc <tpxtrime@mail.ru>
 */

namespace Classes;

/**
 * Class for working with products images
 * @package Classes
 */
class Image {
    
    /**
     * Delete all products images in database and disk
     *
     * @return void
     */
    public static function clearImages()
    {
        mysql_query("TRUNCATE TABLE `cscart_product_descriptions`");
        mysql_query("TRUNCATE TABLE `cscart_product_prices`");
    }
}