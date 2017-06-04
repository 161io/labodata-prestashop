<?php
/**
 * LaboData for Prestashop
 *
 * @author 161 SARL <contact@161.io>
 * @copyright (c) 161 SARL, https://161.io
 * @license https://161.io
 */

namespace LaboDataPrestaShop\Api;

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
     * Mini-cache des categories
     *
     * @param string $key
     * @return array|null
     */
    protected function getCache($key)
    {
        $filename = _PS_CACHE_DIR_ . 'module_labodata_'.$key.'.php';
        if (file_exists($filename)) {
            $dump = include $filename;
            if (isset($dump['time'], $dump['data']) && $dump['time'] == date('ymdH')) {
                return $dump['data'];
            }
        }
        return null;
    }

    /**
     * @param string $key
     * @param array $data
     * @return self
     */
    protected function setCache($key, $data)
    {
        $filename = _PS_CACHE_DIR_ . 'module_labodata_'.$key.'.php';

        $dump = array(
            'time' => date('ymdH'),
            'data' => $data,
        );
        file_put_contents($filename, '<?php return ' . var_export($dump, true) . ';');
        return $this;
    }

    /**
     * Supprimer le mini-cache lors de la desintallation
     */
    public function deleteCache()
    {
        $files = glob(_PS_CACHE_DIR_ . 'module_labodata_*.php');
        foreach ($files as $filename) {
            if (is_file($filename)) {
                @unlink($filename);
            }
        }
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

        $data = $this->getCache('brands');
        if ($data) {
            $this->brands = $data;
            return $this->brands;
        }

        $result = $this->query(self::URL . self::API . '/category/brand.json');
        if (!empty($result['brands'])) {
            $this->brands = $result['brands'];
            $this->setCache('brands', $this->brands);
        } else {
            $this->brands = array();
        }
        return $this->brands;
    }

    /**
     * Toutes les criteres
     *
     * @return array
     */
    public function getCategories()
    {
        if (null !== $this->categories) {
            return $this->categories;
        }

        $data = $this->getCache('categories');
        if ($data) {
            $this->categories = $data;
            return $this->categories;
        }

        $result = $this->query(self::URL . self::API . '/category/criteria.json');
        if (!empty($result['categories'])) {
            $this->categories = $result['categories'];
            $this->setCache('categories', $this->categories);
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
            $types[] = array(
                'name'     => self::TYPE_BRAND,
                'title_fr' => 'Marque',
            );
        }
        foreach ($categories as $type) {
            if (empty($type['items'])) {
                continue;
            }
            unset($type['items']);

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
     * Retourner les categories selon le type
     *
     * @param string $typeName
     * @return array
     */
    public function getCategoriesByName($typeName)
    {
        if (self::TYPE_BRAND == $typeName) {
            return $this->getBrands();
        }

        $categories = $this->getCategories();
        if (!empty($categories[$typeName]['items'])) {
            return $categories[$typeName]['items'];
        }
        return array();
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
        );
        foreach ($this->getBrands() as $brand) {
            if ($brand['id'] == $id) {
                $brand['type'] = $cType;
                return $brand;
            }
        }

        foreach ($this->getCategories() as $type) {
            $cType = $type;
            unset($cType['items']);

            foreach ($type['items'] as $category) {
                if ($category['id'] == $id) {
                    $category['type'] = $cType;
                    return $category;
                }
            }
        }

        return null;
    }
}
