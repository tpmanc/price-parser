<?php
/**
 * @author tpmanc <tpxtrime@mail.ru>
 */

namespace Classes;

/**
 * Class to work with categories
 * @package Classes
 */
class Categories {

    /**
     * Delete all records of top menu in database
     *
     * @return void
     */
    public static function clearCategories()
    {
        mysql_query("TRUNCATE TABLE `cscart_category_descriptions`");
        mysql_query("TRUNCATE TABLE `cscart_categories`");
        mysql_query("DELETE FROM cscart_static_data WHERE param_5=2"); // delete top menu from frontend, 2 - menu id if database
    }

    /**
     * Insert all categories from input array to database
     * @param array $categories Array with categories info
     * @return mixed Return true or error
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
        $inStr1 = "INSERT INTO cscart_category_descriptions(category_id, lang_code, category, description, meta_keywords, meta_description, page_title, age_warning_message) VALUES";
        $inStr2 = "INSERT INTO cscart_categories(category_id, parent_id, id_path, level, company_id, usergroup_ids, status, product_count, position, timestamp, is_op, localization, age_verification, age_limit, parent_age_verification, parent_age_limit, selected_views, default_view, product_details_view, product_columns, yml_market_category, yml_disable_cat) VALUES";
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

        mysql_query($inStr1) or die(mysql_error());
        mysql_query($inStr2) or die(mysql_error());

        return true;
    }
}