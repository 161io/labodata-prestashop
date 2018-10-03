<?php
/**
 * LaboData for Prestashop
 *
 * @author 161 SARL <contact@161.io>
 * @copyright (c) 161 SARL, https://161.io
 * @license https://161.io
 */

namespace LaboDataPrestaShop\Stdlib;

class ObjectModel
{
    /**
     * Retourner le nom de l'objet
     *
     * @param \ObjectModel $object
     * @param mixed $default
     * @return string|null
     */
    public static function getName($object, $default = null)
    {
        if ($object instanceof \Category) {
            return $object->getName();
        }
        if ($object instanceof \FeatureValue) {
            return $object->value ? current($object->value) : $default;
        }
        if ($object instanceof \Manufacturer) {
            return $object->name;
        }
        return $default;
    }
}
