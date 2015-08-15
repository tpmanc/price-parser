<?php
/**
 * @author tpmanc <tpxtrime@mail.ru>
 */
namespace Classes;

use Classes\Settings;

/**
 * Class for working with products images
 * @package Classes
 */
class Image
{
	/**
	 * Delete all products images from database and disk
	 *
	 * @return boolean
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
		if ($q === false) {
			return false;
		}

		if (empty($idArr)) {
			return true;
		}
		
		// delete files
        $imageFolder = Settings::get('imageFolder');
		while ($r = mysql_fetch_array($q)) {
			$folderName = floor($r['detailed_id'] / 1000);
			$idArr[] = $r['detailed_id'];
			unlink($imageFolder . $folderName . '/' . $r['image_path']);
		}

		// delete from database
		$res1 = mysql_query('DELETE FROM cscart_images WHERE image_id in (' . implode(',', $idArr) . ')');
		$res2 = mysql_query('DELETE FROM cscart_images_links WHERE object_type="product" AND  detailed_id in (' . implode(',', $idArr) . ')');
        if ($res1 === false && $res2 === false) {
            return false;
        } else {
            return true;
        }
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
		$res3 = self::replaceImages();

		return $res1 * $res2 * $res3;
	}

    /**
     * Delete images from DB and disk by array of images id
     *
     * @param array $imagesIdArr Array with images id
     * @return boolean
     */
    public static function deleteImagesById(array $imagesIdArr)
    {
        $q = mysql_query('SELECT cscart_images_links.detailed_id, cscart_images.image_path
						  FROM cscart_images_links
						  LEFT JOIN cscart_images
							  ON cscart_images.image_id = cscart_images_links.detailed_id
						  WHERE object_type="product" AND cscart_images_links.detailed_id in ('.implode(',', $imagesIdArr).')
						  ');
        if ($q === false) {
            return false;
        }
        $imageFolder = Settings::get('imageFolder');
        while ($r = mysql_fetch_array($q)) {
            $folderName = floor($r['detailed_id'] / 1000);
            unlink($imageFolder . $folderName . '/' . $r['image_path']);
        }

        $res1 = mysql_query('DELETE FROM cscart_images WHERE image_id in ('.implode(',', $imagesIdArr).')');
        $res2 = mysql_query('DELETE FROM cscart_images_links WHERE detailed_id in ('.implode(',', $imagesIdArr).')');

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
        $imageFolder = Settings::get('imageFolder');
        $downloadSuccess = [];

        $rollingCurl = new \RollingCurl();

        foreach ($images as $i) {
            $rollingCurl->get($i['pictureUrl']);
        }

        $rollingCurl
            ->setCallback(function(\Request $request, \RollingCurl $rollingCurl)  use (&$imageFolder, &$downloadSuccess) {
                $productId = mb_strcut($request->getUrl(), strpos($request->getUrl(), '#') + 1);
                file_put_contents($imageFolder . $productId.'.jpg', $request->getResponseText());
                $downloadSuccess[] = $productId;
            })
            ->setSimultaneousLimit(10)
            ->execute();

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
        $imageFolder = Settings::get('imageFolder');
		foreach ($imagesNames as $i) {
			$size = getimagesize($imageFolder . $i . '.jpg');
			if ($size !== false) {
				$width = $size[0];
				$height= $size[1];
				$inArr[] = '("'.$i.'.jpg", '.$width.', '.$height.')';
			}
		}

        $res = true;
        if (!empty($inArr)) {
            $inStr = $inStr . implode(',', $inArr);
            $res = mysql_query($inStr);
        }

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

        $res = true;
        if (!empty($inArr)) {
            $inStr = $inStr . implode(',', $inArr);
            $res = mysql_query($inStr);
        }

		return ($res === false) ? false : true;
	}

	/**
	 * Replace images from general folder to sub folders
	 *
	 * @return boolean
	 */
	private static function replaceImages()
	{
		$pathArr = [];
        $imageFolder = Settings::get('imageFolder');
		if ($handle = opendir($imageFolder)) {
			while (false !== ($entry = readdir($handle))) {
				if ($entry != "." && $entry != "..") {
					$pathArr[] = '"' . $entry . '"';
				}
			}
			closedir($handle);
		}

		if (count($pathArr) > 0) {
			$q = mysql_query('SELECT * FROM cscart_images WHERE image_path in (' . implode(',', $pathArr) . ')');
			while ($r = mysql_fetch_array($q)) {
				$folderName = floor($r['image_id'] / 1000);
				if (!is_dir($imageFolder . $folderName)) {
					mkdir($imageFolder . $folderName);
				}
				rename($imageFolder . $r['image_path'], $imageFolder . $folderName . '/' .$r['image_path']);
			}
		}

		// remove not replaced images
        self::rmNotReplaceImages();

		return true;
	}

    /**
     * Remove not replaced images
     *
     * @return boolean
     */
    private static function rmNotReplaceImages()
    {
        $imageFolder = Settings::get('imageFolder');
        if ($handle = opendir($imageFolder)) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != "..") {
                    if (strpos($entry, '.jpg') !== false) {
                        unlink($imageFolder . $entry);
                    }
                }
            }
            closedir($handle);
        }

        return true;
    }
}