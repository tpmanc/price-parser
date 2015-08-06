<?php
/**
 * @author tpmanc <tpxtrime@mail.ru>
 */

namespace Classes;

use Tygh\Registry;

/**
 * Class to work with categories
 * @package Classes
 */
class Categories
{

    /**
     * Delete all records of top menu in database
     *
     * @return boolean
     */
    public static function clearCategories()
    {
        $exclude = Registry::get('addons.price_parser.excludeCategories');
        $exclude = str_replace(' ', '', $exclude);
        $excludeArr = explode(',', $exclude);
        $inArr = [];
        foreach ($excludeArr as $e) {
            $inArr[] = '"' . $e . '"';
        }

        $idArr = [];
        $q = mysql_query('SELECT category_id FROM cscart_category_descriptions WHERE category in (' . implode(',', $inArr) . ')');
        if ($q !== false) {
            while ($r = mysql_fetch_array($q)) {
                $idArr[] = $r['category_id'];
            }
        }


        $res1 = mysql_query("DELETE FROM `cscart_category_descriptions` WHERE category_id NOT IN (" . implode(',', $idArr) . ")");
        $res2 = mysql_query("DELETE FROM `cscart_categories` WHERE category_id NOT IN (" . implode(',', $idArr) . ")");
        // $res3 = mysql_query("DELETE FROM cscart_static_data WHERE param_5=2"); // delete top menu from frontend, 2 - menu id if database
        if ($res1 === false && $res2 === false && $res3 === false) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Insert all categories from input array to database
     * @param array $categories Array with categories info
     * @return boolean
     */
    public static function insertCategories($categories)
    {
        /*
         * Таблица cscart_categories
         * id_path - путь из id до категории, например, 166/167/165
         * level - уровень вложенности 1,2,3...
         * status - А-включено, D-выключено, ?-скрыто
         *
         * cscart_static_data - ($param_id, "", "", !, "", 2, "ty-menu-item__products", "A", "A", $position, $parent_id, $id_path, "", 1)
         * cscart_static_data_descriptions - ($param_id, "ru", "$title")
         */
        $inStr1 = "REPLACE INTO cscart_category_descriptions(category_id, lang_code, category, description, meta_keywords, meta_description, page_title, age_warning_message) VALUES";
        $inStr2 = "REPLACE INTO cscart_categories(category_id, parent_id, id_path, level, company_id, usergroup_ids, status, product_count, position, timestamp, is_op, localization, age_verification, age_limit, parent_age_verification, parent_age_limit, selected_views, default_view, product_details_view, product_columns, yml_market_category, yml_disable_cat) VALUES";
        $inArr1 = [];
        $inArr2 = [];
        $position = 0;
        foreach ($categories as $c) {
            $level = 1; // level counter
            $idPathArr = [
                $c['id']
            ];
            $parentId = $c['parentId'];
            while ($parentId !== null) {
                $idPathArr[] = $categories[$parentId]['id'];
                $level++;
                $parentId = $categories[$parentId]['parentId'];
            }
            $idPathArr = array_reverse($idPathArr);
            $par = $c['parentId'];
            if ($par == null) {
                $par = 0;
            }
            $position += 10;
            $inArr1[] = '(' . $c['id'] . ', "ru", "' . mysql_real_escape_string($c['title']) . '", "", "", "", "", "")';
            $inArr2[] = '(' . $c['id'] . ',' . $par . ',"' . implode('/', $idPathArr) . '",' . $level . ',1,0,"A",0,' . $position . ',' . time() . ',"N","","N",0,"N",0,"","","default",0,"","N")';
        }
        $inStr1 = $inStr1 . implode(',', $inArr1);
        $inStr2 = $inStr2 . implode(',', $inArr2);

        $res1 = mysql_query($inStr1);
        $res2 = mysql_query($inStr2);

        return $res1 * $res2;
    }

    /**
     * Update categories
     * Delete categories that are not in the price list, insert new categories
     * @param array $categories Array with categories amounts
     * @param array $images Array with images for categories
     * @return bool
     */
    public static function updateCategories(array $categories)
    {
        $dbCategoriesId = []; // storage for id off all categories in database
        $inputCategoriesId = []; // storage for id off all categories in input array
        $arrForInsert = [];
        $arrIdForInsert = [];

        // check for insert categories
        foreach ($categories as $p) {
            if (!empty($p)) {
                $q = mysql_query('SELECT category_id FROM cscart_categories WHERE category_id=' . $p['id']);
                if ($q !== false && $q !== null) {
                    $r = mysql_result($q, 0);
                    if ($r == null) {
                        $arrForInsert[] = $p;
                        $arrIdForInsert[] = $p['id'];
                    }
                    $inputCategoriesId[] = (int)$p['id'];
                } else {
                    $inputCategoriesId[] = (int)$p['id'];
                }
            }
        }
        $res1 = true;
        if (!empty($arrForInsert)) {
            $res1 = self::insertCategories($arrForInsert);
        }

        // check for delete
        $q = mysql_query('SELECT category_id FROM cscart_categories');
        while ($r = mysql_fetch_array($q)) {
            $dbCategoriesId[] = (int)$r['category_id'];
        }

        $exclude = Registry::get('addons.price_parser.excludeCategories');
        $exclude = str_replace(' ', '', $exclude);
        $excludeArr = explode(',', $exclude);
        $inArr = [];
        var_dump($excludeArr);
        foreach ($excludeArr as $e) {
            $inArr[] = '"' . $e . '"';
        }
        $idArr = []; // exclude
        $q = mysql_query('SELECT category_id FROM cscart_category_descriptions WHERE category in (' . implode(',', $inArr) . ')');
        var_dump('SELECT category_id FROM cscart_category_descriptions WHERE category in (' . implode(',', $inArr) . ')');
        if ($q !== false) {
            while ($r = mysql_fetch_array($q)) {
                $idArr[] = $r['category_id'];
            }
        }
        $arrForDelete = array_diff($dbCategoriesId, $inputCategoriesId, $idArr);
        var_dump($arrForDelete);die();

        $res2 = true;
        $res3 = true;
        if (!empty($arrForDelete)) {
            $res2 = mysql_query('DELETE FROM cscart_categories WHERE category_id in ('.implode(',', $arrForDelete).')');
            $res3 = mysql_query('DELETE FROM cscart_category_descriptions WHERE category_id in ('.implode(',', $arrForDelete).')');
        }

        return $res1 * $res2 * $res3;
    }
}