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
     * @param array $images Array with web paths to images
     * @return boolean
     */
    public static function downloadAndLink(array $images)
    {
        $downloadSuccess = self::downloadImages($images);
        $res1 = self::insertToDb($downloadSuccess);
        $res2 = self::linkWithProducts($downloadSuccess);

        return $res1 * $res2;
    }

    /**
     * Split input array for chunks and download each chunk
     *
     * @param array $images Array with web paths to images
     * @return array Array with products id of successful downloaded images
     */
    private static function downloadImages(array $images)
    {
        $chunkSize = 10;
        $downloadSuccess = [];
        $mh = curl_multi_init();
        $chs = [];
        for ($i = 0; $i < $chunkSize; $i++) {
            $chs[] = ($ch = curl_init());
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        }

        $imagesCount = count($images);
        $lastImgPos = 0;
        $iterCount = ($imagesCount / $chunkSize) + 1;
        for ($i = 0; $i < $iterCount; $i++) {
            for ($j = 0; $j < $chunkSize; $j++) {
                curl_setopt($chs[$j], CURLOPT_URL, $images[$lastImgPos]['pictureUrl']);
                $lastImgPos++;
            }

            $downloadSuccess = array_merge($downloadSuccess, self::downloadImagesChunk($mh, $chs));

            if ($i == 1){break;}
        }

        foreach ($chs as $ch) {
            curl_close($ch);
        }
        curl_multi_close($mh);

        return $downloadSuccess;
    }

    /**
     * Downloading chunk of image array
     *
     * @param resource $mh Multi curl resource
     * @param array $chs Array of curl resources
     * @return array Array of product id for successful downloaded images
     */
    private static function downloadImagesChunk($mh, array $chs)
    {
        $downloadSuccess = [];

        foreach ($chs as $ch) {
            curl_multi_add_handle($mh, $ch);
        }

        $prev_running = $running = null;
        // downloading
        do {
            curl_multi_exec($mh, $running);
            if ($running != $prev_running) {
                $info = curl_multi_info_read($mh);
                if (is_array($info) && ($ch = $info['handle'])) {
                    // получаю содержимое загруженной страницы
                    $content = curl_multi_getcontent($ch);
                    if (!$info['result']) {
                        $url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
                        $productId = mb_strcut($url, strpos($url, '&id=') + 4);
                        $fp = fopen(self::$imageFolder . $productId . '.jpg', 'w+');
                        fwrite($fp, $content);
                        fclose($fp);
                        $downloadSuccess[] = $productId;
                    }
                }

                $prev_running = $running;
            }
        } while ($running > 0);

        foreach ($chs as $ch) {
            curl_multi_remove_handle($mh, $ch);
        }

        return $downloadSuccess;
    }

    /**
     * Insert records with new images to Database
     *
     * @param array $imagesNames Array with names of new images
     * @return boolean
     */
    private static function insertToDb(array $imagesNames)
    {
        $inStr = 'INSERT INTO cscart_images(image_path, image_x, image_y) VALUES ';
        $inArr = [];
        foreach ($imagesNames as $i) {
            $size = getimagesize(self::$imageFolder . $i . '.jpg');
            if ($size !== false) {
                $width = $size[0];
                $height= $size[1];
                $inArr[] = '("'.$i.'.jpg", '.$width.', '.$height.')';
            }
        }

        $inStr = $inStr . implode(',', $inArr);
        $res = mysql_query($inStr) or die(mysql_error());

        return ($res === false) ? false : true;
    }

    /**
     * Insert image link with product in Database
     *
     * @param array $downloadSuccess Array with names of new images
     * @return boolean
     */
    private static function linkWithProducts(array $downloadSuccess)
    {
        $inStr = 'INSERT INTO cscart_images_links(object_id, object_type, image_id, detailed_id, type, position) VALUES';
        $inArr = [];
        $where = [];
        foreach ($downloadSuccess as $i) {
            $where[] = '"'.$i . '.jpg"';
        }
        $q = mysql_query('SELECT image_id, image_path FROM cscart_images WHERE image_path in ('.implode(',', $where).')');
        while ($r = mysql_fetch_array($q)) {
            $productId = str_replace('.jpg', '', $r['image_path']);
            $inArr[] = '('.$productId.', "product", 0, '.$r['image_id'].', "M", 0)';
        }

        $inStr = $inStr . implode(',', $inArr);
        $res = mysql_query($inStr) or die(mysql_error());

        return ($res === false) ? false : true;
    }
}