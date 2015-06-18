<?php
/**
 * @author tpmanc <tpxtrime@mail.ru>
 */

namespace Classes;

/**
 * Class for working with products images
 * @package Classes
 */
class Image
{
    /**
     * @var string Product images folder
     */
    private static $imageFolder = '/var/www/sc/images/detailed/1/';

    /**
     * Delete all products images in database and disk
     *
     * @return void
     */
    public static function clearImages()
    {
        // TODO: delete product images
        //mysql_query("TRUNCATE TABLE `cscart_`");
        //mysql_query("TRUNCATE TABLE `cscart_product_prices`");
    }

    /**
     * Image downloading and saving to Database
     *
     * @param string $imagePath Web path to image
     * @param string $imageName Name of new file
     * @param integer $productId Product id
     * @return bool
     */
    public static function downloadAndLink($imagePath, $imageName, $productId)
    {
        $res1 = self::downloadImage($imagePath, $imageName);
        $imageId = self::insertToDb($imageName);
        $res2 = self::linkWithProduct($imageId, $productId);
        return $res1 * $res2;
    }

    /**
     * Image downloading
     *
     * @param string $imagePath Web path to image
     * @param string $fileName Name of new file
     * @return bool
     */
    private static function downloadImage($imagePath, $fileName)
    {
        $result = file_put_contents(self::$imageFolder . $fileName, file_get_contents($imagePath));
        return $result === false ? false : true;
    }

    /**
     * Insert record with new image to Database
     *
     * @param string $fileName Name of new file
     * @return mixed
     */
    private static function insertToDb($fileName)
    {
        $size = getimagesize(self::$imageFolder . $fileName);
        if ($size !== false) {
            $width = $size[0];
            $height= $size[1];
            $result = mysql_query('INSERT INTO cscart_images(image_path, image_x, image_y) VALUES ("'.$fileName.'",'.$width.', '.$height.')');
            if ($result === false) {
                return false;
            } else {
                return mysql_insert_id();
            }
        }
        return false;
    }

    /**
     * Insert image link with product in Database
     *
     * @param integer $imageId Image id
     * @param integer $productId Product id
     * @return mixed
     */
    private static function linkWithProduct($imageId, $productId)
    {
        $result = mysql_query('INSERT INTO cscart_images_links(object_id, object_type, image_id, detailed_id, type, position) VALUES (
                                '.$productId.',
                                "product",
                                0,
                                '.$imageId.',
                                "M",
                                0
                                )');
        return $result === false ? false : true;
    }
}