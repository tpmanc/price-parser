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
     */
}