<?php
/**
 * @author tpmanc <tpxtrime@mail.ru>
 */

namespace Classes;

/**
 * Class for work with products properties
 * @package Classes
 */
class Properties
{
    /*
     * cscart_product_features - ($id, "", 1, $type, $categories, $parentId, $dOnProduct, $dOnCatalog, $dOnHeader, "A", $position, "N")
     * $categories - перечесление категорий через запятую, в которых будет доступна эта характеристика
     * $dOnProduct - Отображать в карточке товара
     * $dOnCatalog - Отображать в списке товаров
     * $dOnHeader - Отображать в заголовке карточки
     * $type: Список вариантов: "S" - текст, "N" - число, "E" - Бренд/производитель; Другие: "T" - текст, "O" - число, "D" - дата; Флажок: "C" - один, "M" - несколько
     *
     * cscart_product_features_descriptions - ($id, $title, "", "", "", "ru")
     *
     * cscart_product_features_values - ($featureId, $productId, $variantId, $textValue|"", $intValue|null, "ru")
     * для чекбокса (например, поддержка 3D) - ($featureId, $productId, 0, "N"|"Y", null, "ru")
     * для числа (например, поддержка 3D) - ($featureId, $productId, 0, "", 6, "ru")
     * для текста (например, поддержка 3D) - ($featureId, $productId, 0, "text here", null, "ru")
     *
     * cscart_product_feature_variant_descriptions - ($variantId, $variant, "", "", "", "", "ru")
     * здесь, наверно, храняться варианты для разных типов характеристик
     * т.к. мы везде используем текст, то не заполняем эту таблицу
     *
     * cscart_ult_objects_sharing - ($companyId, $featureId, "product_features")
     * не очень понятно что это, но без этого не работает
     */

    /**
     * Delete all properties in database
     *
     * @return boolean
     */
    public static function clearProperties()
    {
        $res1 = mysql_query("TRUNCATE TABLE `cscart_product_features`");
        $res2 = mysql_query("TRUNCATE TABLE `cscart_product_features_descriptions`");
        $res3 = mysql_query("TRUNCATE TABLE `cscart_product_feature_variant_descriptions`");
        $res4 = mysql_query("TRUNCATE TABLE `cscart_product_features_values`");
        $res5 = mysql_query("DELETE FROM cscart_ult_objects_sharing WHERE share_object_type='product_features'");
        if ($res1 === false && $res2 === false && $res3 === false && $res4 === false && $res5 === false) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Insert properties from input array to database
     * Using tables: cscart_product_features, cscart_product_features_descriptions
     * @param array $properties Properties array
     * @return boolean
     */
    public static function insertProperties(array $properties)
    {
        $inStr1 = 'INSERT INTO cscart_product_features(feature_id, feature_code, company_id, feature_type, categories_path, parent_id, 
                                display_on_product, display_on_catalog, display_on_header, status, position, comparison) VALUES';
        $inStr2 = 'INSERT INTO cscart_product_features_descriptions(feature_id, description, full_description, prefix, suffix, lang_code) VALUES';
        $inStr3 = 'REPLACE INTO cscart_ult_objects_sharing(share_company_id, share_object_id, share_object_type) VALUES';
        $inArr1 = [];
        $inArr2 = [];
        $inArr3 = [];
        $num = 0;
        $position = 0;
        foreach ($properties as $p) {
            $num++;
            $position += 10;
            $id = (int)str_replace('p', '', $p['propertyId']);
            $type = 'T';
            $inArr1[] = '('.$id.', "", 1, "'.$type.'", "", 0, "Y", "Y", "N", "A", '.$position.', "N")';
            $inArr2[] = '('.$id.', "'.mysql_real_escape_string($p['propertyTitle']).'", "", "", "", "ru")';
            $inArr3[] = '(1, '. $id .', "product_features")';
            if ($num == 2000) {
                $num = 0;
                mysql_query($inStr1 . implode(',', $inArr1)) or die(mysql_error() . $inStr1 . implode(',', $inArr1));
                mysql_query($inStr2 . implode(',', $inArr2)) or die(mysql_error() . $inStr2 . implode(',', $inArr2));
                mysql_query($inStr3 . implode(',', $inArr3)) or die(mysql_error() . $inStr3 . implode(',', $inArr3));
                $inArr1 = [];
                $inArr2 = [];
                $inArr3 = [];
            }
        }

        $res1 = mysql_query($inStr1 . implode(',', $inArr1));
        $res2 = mysql_query($inStr2 . implode(',', $inArr2));
        $res3 = mysql_query($inStr3 . implode(',', $inArr3));

        return $res1 * $res2 * $res3;
    }

    /**
     * Insert properties from input array to database
     * Using table: cscart_product_features_values
     * @param array $products Array with properties values for each product
     * @return boolean
     */
    public static function addPropertyToProduct(array $products)
    {
        echo count($products);
        $inStr = 'REPLACE INTO cscart_product_features_values(feature_id, product_id, variant_id, value, value_int, lang_code) VALUES';
        $inArr = [];
        $num = 0;
        foreach ($products as $p) {
            if (is_array($p['productProperties']) && !empty($p['productProperties'])) {
                foreach ($p['productProperties'] as $prop) {
                    $propertyId = (int)str_replace('p', '', $prop['propertyId']);
                    $inArr[] = '('. $propertyId .', '. $p['productId'] .', 0, "'. mysql_real_escape_string($prop['propertyValue']) .'", null, "ru")';
                    $num++;
                    if ($num == 2000) {
                        $num = 0;
                        mysql_query($inStr . implode(',', $inArr)) or die('1. '.mysql_error() . $inStr . implode(',', $inArr));
                        $inArr = [];
                    }
                }
            }
        }
        $res1 = mysql_query($inStr . implode(',', $inArr)) or die(mysql_error() . $inStr . implode(',', $inArr));

        return $res1;
    }

}