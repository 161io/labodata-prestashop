<?php
/**
 * LaboData for Prestashop
 *
 * @author 161 SARL <contact@161.io>
 * @copyright (c) 161 SARL, https://161.io
 * @license https://161.io
 */

namespace LaboDataPrestaShop\Api;

use Category;
use LaboDataPrestaShop\Import\ImportCategory;
use LaboDataPrestaShop\Stdlib\Cache;
use LaboDataPrestaShop\Stdlib\ObjectModel;

/**
 * Chargement et manipulation de l'arborescence des categories LaboData
 *
 * @method static Tree getInstance()
 */
class Tree extends Query
{
    /**
     * @const string
     */
    const TYPE_PUBLIC_CATEGORIES = 'public_categories';

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
            $this->getCategories();
        }
        return $this->langs;
    }

    /**
     * Retourner Les categories de l'arborescence
     *
     * @return array
     */
    public function getCategories()
    {
        if (null !== $this->categories) {
            return $this->categories;
        }

        $langs = Cache::get('langs');
        $categories = Cache::get('tree');
        if ($langs && $categories) {
            $this->setLangs($langs);
            $this->categories = $categories;
            return $this->categories;
        }

        $result = $this->query(self::URL . self::API . '/category/tree.json');
        $this->setLangsFromResult($result);
        if (!empty($result['tree'])) {
            $categories = $this->setCategoryTitles($result['tree']);
            $this->categories = $categories;
            Cache::set('tree', $this->categories);
        } else {
            $this->categories = array();
        }
        return $this->categories;
    }

    /**
     * Uniquement les types d'arborescence
     *
     * @return array
     */
    public function getCategoryTypes()
    {
        $categories = $this->getCategories();
        if (!$categories) {
            return array();
        }

        $types = array();
        foreach ($categories as $type) {
            if (empty($type['items'])) {
                continue;
            }
            unset($type['items']);
            $type['title'] = $this->getTransItem($type);

            $types[] = $type;
        }

        return $types;
    }

    /**
     * Uniquement les slugs des categories de l'arborescence
     *
     * @return array
     */
    public function getCategoryTypeNames()
    {
        $categories = $this->getCategories();
        if (!$categories) {
            return array();
        }

        $names = array();
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
        return self::TYPE_PUBLIC_CATEGORIES;
    }

    /**
     * Retourner les categories de l'arborescence selon le type
     *
     * @param string $typeName
     * @return array
     */
    public function getCategoriesByName($typeName)
    {
        $categories = $this->getCategories();
        if (empty($categories[$typeName]['items'])) {
            return array();
        }

        return $this->getCategoriesByNameRecurs($categories[$typeName]['items']);
    }

    /**
     * @param array $items
     * @param string $parentTitle LaboData
     * @return array
     */
    protected function getCategoriesByNameRecurs($items, $parentTitle = '')
    {
        if (empty($items)) {
            return array();
        }

        $importInstance = ImportCategory::getInstance();
        $array = array();
        foreach ($items as $item) {
            $item['id_prestashop'] = '';
            $item['title_prestashop'] = '';
            $idPrestashop = $importInstance->getIdCategoryByIdLabodata($item['id']);
            if ($idPrestashop) {
                $objectPs = new Category($idPrestashop);
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
                $this->getCategoriesByNameRecurs($item['children'], $item['title'])
            );
        }
        return $array;
    }

    /**
     * Retourner une categorie de l'arboresence selon son ID
     *
     * @param int $id
     * @return array|null
     */
    public function getCategoryById($id)
    {
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
