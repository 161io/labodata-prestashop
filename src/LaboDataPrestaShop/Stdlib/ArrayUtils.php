<?php
/**
 * LaboData for Prestashop
 *
 * @author 161 SARL <contact@161.io>
 * @copyright (c) 161 SARL, https://161.io
 * @license https://161.io
 */

namespace LaboDataPrestaShop\Stdlib;

class ArrayUtils
{
    /**
     * @param array $array
     * @param string $columnKey
     * @param string $indexKey
     * @return array
     * @see array_column
     */
    public static function arrayColumn($array, $columnKey, $indexKey)
    {
        if (function_exists('array_column')) { // PHP 5.5+
            return array_column($array, $columnKey, $indexKey);
        }

        $return = array();
        foreach ($array as $_array) {
            $return[$_array[$indexKey]] = $_array[$columnKey];
        }
        return $return;
    }
}
