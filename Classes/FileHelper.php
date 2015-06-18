<?php
/**
 * @author tpmanc <tpxtrime@mail.ru>
 */

namespace Classes;
/**
 * Downloading and unzipping
 */
class FileHelper
{
    /**
     * Download file from $url to $filePath folder
     * @param string $url File location
     * @param string $filePath File destination path
     * @return boolean Boolean result of operation
     */
    public static function download($url, $filePath){
        $result = file_put_contents($filePath, fopen($url, 'r'));
        return $result !== false ? true : false;
    }

    /**
     * Unzip archive $file
     * @param string $file Zip archive location
     * @param string $extractTo Destination path
     * @return boolean Result of operation
     */
    public static function unzip($file, $extractTo = './'){
        $zip = new \ZipArchive;
        if ($zip->open($file) === true) {
            $zip->extractTo($extractTo);
            $zip->close();
            return true;
        } else {
            return false;
        }
    }
}