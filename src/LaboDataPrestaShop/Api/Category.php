<?php
/**
 * LaboData for Prestashop
 *
 * @author 161 SARL <contact@161.io>
 * @copyright (c) 161 SARL, https://161.io
 * @license https://161.io
 */

namespace LaboDataPrestaShop\Api;

use FeatureValue;
use Manufacturer;
use LaboDataPrestaShop\Import\ImportFeature;
use LaboDataPrestaShop\Import\ImportManufacturer;
use LaboDataPrestaShop\Stdlib\Cache;
use LaboDataPrestaShop\Stdlib\ObjectModel;

/**
 * Chargement et manipulation des categories LaboData
 *
 * @method static Category getInstance()
 */
class Category extends Query
{
    /**
     * @const string Table des marques
     */
    const DB_TABLE_MANUFACTURER = 'manufacturer_labodata';

    /**
     * @const string Table des caracteries (valeurs)
     */
    const DB_TABLE_FEATURE_VALUE = 'feature_value_labodata';

    /**
     * @const string Table des categories
     */
    const DB_TABLE_CATEGORY = 'category_labodata';

    /**
     * @const string
     */
    const TYPE_BRAND = 'brand';

    /**
     * @var array
     */
    protected $brands;

    /**
     * @var array
     */
    protected $categories;

    /**
     * @return array
     */
    public function getLangs()
    {
        if (null === $this->langs) {
            $this->getBrands();
        }
        return $this->langs;
    }

    /**
     * Retourner Les marques
     *
     * @return array
     */
    public function getBrands()
    {
        if (null !== $this->brands) {
            return $this->brands;
        }

        $langs = Cache::get('langs');
        $categories = Cache::get('brands');
        if ($langs && $categories) {
            $this->setLangs($langs);
            $this->brands = $categories;
            return $this->brands;
        }

        $result = $this->query(self::URL . self::API . '/category/brand.json');
        $this->setLangsFromResult($result);
        if (!empty($result['brands'])) {
            $categories = $this->setCategoryTitles($result['brands']);
            $this->brands = $categories;
            Cache::set('brands', $categories);
        } else {
            $this->brands = array();
        }
        return $this->brands;
    }

    /**
     * Tous les criteres
     *
     * @return array
     */
    public function getCategories()
    {
        if (null !== $this->categories) {
            return $this->categories;
        }

        $langs = Cache::get('langs');
        $categories = Cache::get('categories');
        if ($langs && $categories) {
            $this->setLangs($langs);
            $this->categories = $categories;
            return $this->categories;
        }

        $result = $this->query(self::URL . self::API . '/category/criteria.json');
        $this->setLangsFromResult($result);
        if (!empty($result['categories'])) {
            $categories = $this->setCategoryTitles($result['categories']);
            $this->categories = $categories;
            Cache::set('categories', $categories);
        } else {
            $this->categories = array();
        }
        return $this->categories;
    }

    /**
     * Uniquement les types de criteres
     *
     * @param bool $withBrand
     * @return array
     */
    public function getCategoryTypes($withBrand = true)
    {
        $categories = $this->getCategories();
        if (!$categories) {
            return array();
        }

        $types = array();
        if ($withBrand) {
            $type = array(
                'name'     => self::TYPE_BRAND,
                'title_fr' => 'Marque',
                'title_en' => 'Brand',
            );
            $type['title'] = $this->getTransArray($type);

            $types[] = $type;
        }
        foreach ($categories as $type) {
            if (empty($type['items'])) {
                continue;
            }
            unset($type['items']);
            $type['title'] = $this->getTransArray($type);

            $types[] = $type;
        }

        return $types;
    }

    /**
     * Uniquement les slugs des criteres
     *
     * @param bool $withBrand
     * @return array
     */
    public function getCategoryTypeNames($withBrand = true)
    {
        $categories = $this->getCategories();
        if (!$categories) {
            return array();
        }

        $names = array();
        if ($withBrand) {
            $names[] = self::TYPE_BRAND;
        }
        foreach ($categories as $type) {
            if (empty($type['items'])) {
                continue;
            }
            $names[] = $type['name'];
        }
        return $names;
    }

    /**
     * @return string
     */
    public function getDefaultTypeName()
    {
        return self::TYPE_BRAND;
    }

    /**
     * Retourner les categories selon le type
     *
     * @param string $typeName
     * @return array
     */
    public function getCategoriesByName($typeName)
    {
        if (self::TYPE_BRAND == $typeName) {
            return $this->getCategoriesByNameRecurs(true, $this->getBrands());
        }

        $categories = $this->getCategories();
        if (empty($categories[$typeName]['items'])) {
            return array();
        }

        return $this->getCategoriesByNameRecurs(false, $categories[$typeName]['items']);
    }

    /**
     * @param bool $isManufacturer sinon Feature
     * @param array $items
     * @param string $parentTitle LaboData
     * @return array
     */
    protected function getCategoriesByNameRecurs($isManufacturer, $items, $parentTitle = '')
    {
        if (empty($items)) {
            return array();
        }

        if ($isManufacturer) {
            $importInstance = ImportManufacturer::getInstance();
        } else {
            $importInstance = ImportFeature::getInstance();
        }
        $array = array();
        foreach ($items as $item) {
            $item['id_prestashop'] = '';
            $item['title_prestashop'] = '';
            if ($isManufacturer) {
                $idPrestashop = $importInstance->getIdManufacturerByIdLabodata($item['id']);
            } else {
                $idPrestashop = $importInstance->getIdFeatureValueByIdLabodata($item['id']);
            }
            if ($idPrestashop) {
                if ($isManufacturer) {
                    $objectPs = new Manufacturer($idPrestashop);
                } else {
                    $objectPs = new FeatureValue($idPrestashop);
                }

                if ($objectPs->id) {
                    $item['id_prestashop'] = (int) $objectPs->id;
                    $item['title_prestashop'] = ObjectModel::getName($objectPs);
                }
            }
            $item['title'] = ($parentTitle ? $parentTitle . ' > ' : '') . $item['title'];
            $array[] = $item;

            if (empty($item['children'])) {
                continue;
            }
            $array = array_merge(
                $array,
                $this->getCategoriesByNameRecurs($isManufacturer, $item['children'], $item['title'])
            );
        }
        return $array;
    }

    /**
     * Retourner une categorie selon son ID
     *
     * @param int $id
     * @return array|null
     */
    public function getCategoryById($id)
    {
        $cType = array(
            'name'     => self::TYPE_BRAND,
            'title_fr' => 'Marque',
            'title_en' => 'Brand',
        );
        $return = $this->getCategoryByIdRecurs($this->getBrands(), $id, $cType);
        if ($return) {
            return $return;
        }

        foreach ($this->getCategories() as $type) {
            $cType = $type;
            unset($cType['items']);

            $return = $this->getCategoryByIdRecurs($type['items'], $id, $cType);
            if ($return) {
                return $return;
            }
        }

        return null;
    }

    /**
     * @param array $items
     * @param int $id
     * @param array $cType
     * @return array|null
     */
    protected function getCategoryByIdRecurs($items, $id, $cType)
    {
        foreach ($items as $item) {
            if ($item['id'] == $id) {
                $item['type'] = $cType;
                return $item;
            }
            if (empty($item['children'])) {
                continue;
            }

            $return = $this->getCategoryByIdRecurs($item['children'], $id, $cType);
            if ($return) {
                return $return;
            }
        }

        return null;
    }
}
