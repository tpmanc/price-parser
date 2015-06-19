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
        $q = mysql_query('SELECT cscart_images_links.detailed_id, cscart_images.image_path
                          FROM cscart_images_links
                          LEFT JOIN cscart_images
                              ON cscart_images.image_id = cscart_images_links.detailed_id
                          WHERE object_type="product"
                          ');
        $idArr = [];
        while ($r = mysql_fetch_array($q)) {
            $idArr[] = $r['detailed_id'];
            unlink(self::$imageFolder . $r['image_path']);
        }
        mysql_query('DELETE FROM cscart_images WHERE image_id in (' . implode(',', $idArr) . ')');
        mysql_query('DELETE FROM cscart_images_links WHERE object_type="product" AND  detailed_id in (' . implode(',', $idArr) . ')');
    }

    /**
     * Image downloading and saving to Database
     *
     * @param string $imagePath Web path to image
     * @param string $imageName Name of new file
     * @param integer $productId Product id
     * @return array
     */
    public static function downloadAndLink($imagePath, $imageName, $productId)
    {
        $res1 = self::downloadImage($imagePath, $imageName);
        if ($res1 === false) {
            return [];
        }
        $imageId = self::insertToDb($imageName);
        $link = self::linkWithProduct($imageId, $productId);
        return [
            'link' => $link,
        ];
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
     * @return string
     */
    private static function linkWithProduct($imageId, $productId)
    {
        return '('.$productId.', "product", 0, '.$imageId.', "M", 0)';
    }
}