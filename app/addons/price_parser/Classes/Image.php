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
        $downloadSuccess = [];
        foreach ($images as $i) {
            $con = file_get_contents($i['pictureUrl']);
            file_put_contents('C:\OpenServer/domains/test/images/detailed/'.$i['productId'].'.jpg', $con);
            $downloadSuccess[] = $i['productId'];
        }

  //       $needInc = false;
  //       if (count($images) > 10) {
  //           $chunkSize = 10;
  //           $needInc = true;
  //       } else {
  //           $chunkSize = count($images);
  //       }
		// $downloadSuccess = [];
		// $mh = curl_multi_init();
		// $chs = [];
		// for ($i = 0; $i < $chunkSize; $i++) {
		// 	$chs[] = ($ch = curl_init());
		// 	curl_setopt($ch, CURLOPT_HEADER, 0);
		// 	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		// }

		// $imagesCount = count($images);
		// $lastImgPos = 0;
		// $iterCount = ($imagesCount / $chunkSize);
  //       if ($needInc) {
  //           $iterCount++;
  //       }
		// for ($i = 0; $i < $iterCount; $i++) {
		// 	for ($j = 0; $j < $chunkSize; $j++) {
		// 		if ($i != $iterCount || isset($images[$lastImgPos])) {
		// 			curl_setopt($chs[$j], CURLOPT_URL, $images[$lastImgPos]['pictureUrl']);
		// 			$lastImgPos++;
		// 		}
		// 	}

		// 	$downloadSuccess = array_merge($downloadSuccess, self::downloadImagesChunk($mh, $chs));
		// }

		// foreach ($chs as $ch) {
		// 	curl_close($ch);
		// }
		// curl_multi_close($mh);

		// return $downloadSuccess;
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
		// картинки сохраняются в папки с названием: целая часть от id картинки / 1000
		// сначала скачиваем в общую папку, потом из нее уже будем рассовывать по подпапкам. Сейчас мы не можем это сделать,
		// т.к. еще не известны id картинок из БД
		$downloadSuccess = [];
        $imageFolder = Settings::get('imageFolder');

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
						$fp = fopen($imageFolder . $productId . '.jpg', 'w+');
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