<?php
/**
 * Copyright (c) 161 SARL, https://161.io
 */

/**
 * Injection des elements LaboData vers Prestashop
 */
class LaboDataPrestashop
{
    /**
     * @var self
     */
    protected static $instance;

    /**
     * @var int[]
     */
    protected $manufacturerLabodataIds;

    /**
     * @var int[]
     */
    protected $categoryLabodataIds;

    /**
     * @var Language
     */
    protected $lang;

    /**
     * @var int[]
     */
    protected $typeIds;

    /**
     * @return self
     */
    public static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Correspondance entre les marques LaboData et les marques Prestashop
     *
     * @return int[]
     */
    public function getManufacturerLabodataIds()
    {
        if (null === $this->manufacturerLabodataIds) {
            // Nettoyage
            $sql = 'DELETE FROM `'._DB_PREFIX_.LaboDataCategory::DB_TABLE_MANUFACTURER.'` WHERE `id_manufacturer` NOT IN (SELECT `id_manufacturer` FROM `'._DB_PREFIX_.'manufacturer`);';
            Db::getInstance(_PS_USE_SQL_SLAVE_)->execute($sql);

            $sql = new DbQuery();
            $sql->from(LaboDataCategory::DB_TABLE_MANUFACTURER);
            $ids = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

            if (function_exists('array_column')) { // PHP 5.5+
                $this->manufacturerLabodataIds = array_column($ids, 'id_manufacturer', 'id_labodata');
            } else {
                foreach ($ids as $_ids) {
                    $this->manufacturerLabodataIds[$_ids['id_labodata']] = $_ids['id_manufacturer'];
                }
            }
        }
        return $this->manufacturerLabodataIds;
    }

    /**
     * @param int $idLabodata
     * @return int|null
     */
    public function getIdManufacturerByIdLabodata($idLabodata)
    {
        $ids = $this->getManufacturerLabodataIds();
        if (isset($ids[$idLabodata])) {
            return (int) $ids[$idLabodata];
        }
        return null;
    }

    /**
     * Correspondance entre les categories LaboData et les categories Prestashop
     *
     * @return int[]
     */
    public function getCategoryLabodataIds()
    {
        if (null === $this->categoryLabodataIds) {
            // Nettoyage
            $sql = 'DELETE FROM `'._DB_PREFIX_.LaboDataCategory::DB_TABLE_CATEGORY.'` WHERE `id_category` NOT IN (SELECT `id_category` FROM `'._DB_PREFIX_.'category`);';
            Db::getInstance(_PS_USE_SQL_SLAVE_)->execute($sql);

            $sql = new DbQuery();
            $sql->from(LaboDataCategory::DB_TABLE_CATEGORY);
            $ids = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

            if (function_exists('array_column')) { // PHP 5.5+
                $this->categoryLabodataIds = array_column($ids, 'id_category', 'id_labodata');
            } else {
                foreach ($ids as $_ids) {
                    $this->categoryLabodataIds[$_ids['id_labodata']] = $_ids['id_category'];
                }
            }
        }
        return $this->categoryLabodataIds;
    }

    /**
     * @param int $idLabodata
     * @return int|null
     */
    public function getIdCategoryByIdLabodata($idLabodata)
    {
        $ids = $this->getCategoryLabodataIds();
        if (isset($ids[$idLabodata])) {
            return (int) $ids[$idLabodata];
        }
        return null;
    }

    /**
     * Langue par defaut
     *
     * @param bool $toInt
     * @return int|Language
     */
    public function getLang($toInt = true)
    {
        if (null === $this->lang) {
            $this->lang = new Language(Configuration::get('PS_LANG_DEFAULT'));
        }
        if ($toInt) {
            return (int) $this->lang->id;
        }
        return $this->lang;
    }

    /**
     * @return string
     */
    public function getLangCode()
    {
        return $this->getLang(false)->iso_code;
    }

    /**
     * Retourner l'identifiant de la categorie type
     *
     * @param array $categoryType
     * @param bool $autoAdd La creer, si elle n'existe pas
     * @return int|null
     */
    public function getTypeId($categoryType, $autoAdd = false)
    {
        if (!isset($categoryType['name'], $categoryType['title_fr'])) {
            return null;
        }
        $name = $categoryType['name'];
        $title = $categoryType['title_fr'];

        if (isset($this->typeIds[$name])) {
            return $this->typeIds[$name];
        }

        //$idParentCategory = Configuration::get('PS_ROOT_CATEGORY');
        $idParentCategory = Configuration::get('PS_HOME_CATEGORY');
        $category = Category::searchByNameAndParentCategoryId($this->getLang(), $title, $idParentCategory);

        if ($category) {
            $this->typeIds[$name] = (int) $category['id_category'];
            return $this->typeIds[$name];
        }

        if (!$autoAdd) {
            return null;
        }

        $category = new Category();
        $category->is_root_category = false;
        $category->id_parent = $idParentCategory;
        $category->active = true;
        $category->name = LaboDataCopyPaste::createMultiLangField($title);
        $category->meta_title = LaboDataCopyPaste::createMultiLangField($title);
        $category->meta_description = LaboDataCopyPaste::createMultiLangField($title);
        $category->link_rewrite = LaboDataCopyPaste::createMultiLangField(Tools::link_rewrite($name));
        $category->add();

        $this->typeIds[$name] = (int) $category->id;
        return $this->typeIds[$name];
    }



    /**
     * Ajouter une marque Prestashop
     *
     * @param array $laboDataCategory
     * @return Manufacturer|null
     */
    public function addManufacturer($laboDataCategory)
    {
        if (!isset($laboDataCategory['id'], $laboDataCategory['type'], $laboDataCategory['name'], $laboDataCategory['title_fr'])) {
            return null;
        }

        $name = $laboDataCategory['name'];
        $title = $laboDataCategory['title_fr'];

        $manufacturer = new Manufacturer();
        $manufacturer->active = true;
        $manufacturer->name = $title;
        $manufacturer->meta_title = LaboDataCopyPaste::createMultiLangField($title);
        $manufacturer->meta_description = LaboDataCopyPaste::createMultiLangField($title);
        $manufacturer->link_rewrite = LaboDataCopyPaste::createMultiLangField(Tools::link_rewrite($name));
        $manufacturer->add();

        $this->_addManufacturerLabodata($manufacturer, $laboDataCategory);

        return $manufacturer;
    }

    /**
     * Jointure entre ids LaboData et Prestashop
     *
     * @param Manufacturer $manufacturer
     * @param array $laboDataCategory
     * @return bool
     */
    protected function _addManufacturerLabodata($manufacturer, $laboDataCategory)
    {
        return Db::getInstance()->insert(LaboDataCategory::DB_TABLE_MANUFACTURER, array(
            'id_manufacturer' => (int) $manufacturer->id,
            'id_labodata'     => (int) $laboDataCategory['id'],
        ));
    }

    /**
     * Ajouter une categorie Prestashop
     *
     * @param array $laboDataCategory
     * @return Category
     */
    public function addCategory($laboDataCategory)
    {
        if (!isset($laboDataCategory['id'], $laboDataCategory['type'], $laboDataCategory['name'], $laboDataCategory['title_fr'])) {
            return null;
        }

        $typeId = $this->getTypeId($laboDataCategory['type'], true);
        if (null === $typeId) {
            return null;
        }

        $name = $laboDataCategory['name'];
        $title = $laboDataCategory['title_fr'];

        $category = new Category();
        $category->is_root_category = false;
        $category->id_parent = $typeId;
        $category->active = true;
        $category->name = LaboDataCopyPaste::createMultiLangField($title);
        $category->meta_title = LaboDataCopyPaste::createMultiLangField($title);
        $category->meta_description = LaboDataCopyPaste::createMultiLangField($title);
        $category->link_rewrite = LaboDataCopyPaste::createMultiLangField(Tools::link_rewrite($name));
        $category->add();

        $this->_addCategoryLabodata($category, $laboDataCategory);

        return $category;
    }

    /**
     * Jointure entre ids LaboData et Prestashop
     *
     * @param Category $category
     * @param array $laboDataCategory
     * @return bool
     */
    protected function _addCategoryLabodata($category, $laboDataCategory)
    {
        return Db::getInstance()->insert(LaboDataCategory::DB_TABLE_CATEGORY, array(
            'id_category' => (int) $category->id,
            'id_labodata' => (int) $laboDataCategory['id'],
        ));
    }



    /**
     * @param LaboDataProduct $laboDataProduct
     * @return Product|null
     */
    public function addProduct($laboDataProduct)
    {
        if (!$laboDataProduct->getId()) {
            return null;
        }

        $product = new Product();
        $product->shop = (int) Configuration::get('PS_SHOP_DEFAULT');
        $product->id_shop_default = $product->shop;
        $product->visibility = 'both';
        $product->condition = 'new';
        $this->_hydrateProduct($product, $laboDataProduct);
        $product->save();

        $prestashopCategories = $this->_convertCategoriesLaboDataToPrestashop($laboDataProduct);
        $product->addToCategories($prestashopCategories);

        $this->_addImage($product, $laboDataProduct);

        return $product;
    }

    /**
     * @param LaboDataProduct $laboDataProduct
     * @return Product|null
     */
    public function editProduct($laboDataProduct)
    {
        if (!$laboDataProduct->getId()) {
            return null;
        }
        $product = $this->_searchProduct($laboDataProduct);
        if (!$product) {
            return $this->addProduct($laboDataProduct);
        }

        $this->_hydrateProduct($product, $laboDataProduct);
        $product->save();

        $this->_addImage($product, $laboDataProduct);

        return $product;
    }

    /**
     * @param LaboDataProduct $laboDataProduct
     * @return Product|null
     */
    protected function _searchProduct($laboDataProduct)
    {
        $products = Product::searchByName($this->getLang(), $laboDataProduct->getEan13());
        if (!$products) { return null; }
        foreach ($products as $product) {
            $productObj = new Product((int) $product['id_product'], false, $this->getLang());
            if ($productObj->id) {
                return $productObj;
            }
        }
        return null;
    }

    /**
     * @param Product $product
     * @param LaboDataProduct $laboDataProduct
     * @return self
     */
    protected function _hydrateProduct($product, $laboDataProduct)
    {
        $smarty = Context::getContext()->smarty;

        // Nom du produit
        if (empty($product->name[$this->getLang()])) {
            $product->name = array($this->getLang() => $laboDataProduct->getTitle($this->getLangCode()));
        }
        if (empty($product->meta_title[$this->getLang()])) {
            $product->meta_title = array($this->getLang() => $laboDataProduct->getTitle($this->getLangCode()));
        }

        // Marque
        if (empty($product->id_manufacturer)) {
            $idManufacturer = $this->getIdManufacturerByIdLabodata($laboDataProduct->getBrandId());
            if (!$idManufacturer) {
                $idManufacturer = Manufacturer::getIdByName($laboDataProduct->getBrandTitle($this->getLangCode()));
            }
            if ($idManufacturer) {
                $product->id_manufacturer = $idManufacturer;
            }
        }

        if (empty($product->reference)) {
            $product->reference = $laboDataProduct->getEan13();
        }
        if (empty($product->ean13)) {
            $product->ean13 = $laboDataProduct->getEan13();
        }
        if (empty($product->link_rewrite[$this->getLang()])) {
            $product->link_rewrite = array($this->getLang() => Tools::link_rewrite($laboDataProduct->getTitle($this->getLangCode())));
        }
        if (empty($product->description_short) && $laboDataProduct->getContent($this->getLangCode())) {
            $smarty->assign(array(
                'description_short' => $laboDataProduct->getContent($this->getLangCode()),
                'descriptions'      => $laboDataProduct->getAdditionalContent($this->getLangCode()),
            ));
            $product->description_short = trim($smarty->fetch(__DIR__ . '/../views/templates/import/description_short.tpl'));
        }
        if (empty($product->description) && $laboDataProduct->getAdditionalContent($this->getLangCode())) {
            $smarty->assign(array(
                'description_short' => $laboDataProduct->getContent($this->getLangCode()),
                'descriptions'      => $laboDataProduct->getAdditionalContent($this->getLangCode()),
            ));
            $product->description = trim($smarty->fetch(__DIR__ . '/../views/templates/import/description.tpl'));
        }
        if (empty($product->id_tax_rules_group)) {
            $product->id_tax_rules_group = $this->getIdTaxRulesGroup($laboDataProduct->getVat());
        }
        if ((!(float) $product->weight) && $laboDataProduct->getWeight()) {
            $product->weight = $laboDataProduct->getWeight() / 1000; // g >> kg
        }

        return $this;
    }

    /**
     * @param LaboDataProduct $laboDataProduct
     * @return int[]
     */
    protected function _convertCategoriesLaboDataToPrestashop($laboDataProduct)
    {
        $laboDataCategory = $laboDataProduct->getCategoryIds();
        if (!$laboDataCategory) {
            return array();
        }

        $prestashopCategories = array();
        foreach ($laboDataCategory as $idLabodata) {
            $prestashopId = $this->getIdCategoryByIdLabodata($idLabodata);
            if (!$prestashopId) { continue; }
            $prestashopCategories[] = $prestashopId;
        }

        return $prestashopCategories;
    }

    /**
     * @param Product $product
     * @param LaboDataProduct $laboDataProduct
     * @return self
     */
    protected function _addImage($product, $laboDataProduct)
    {
        $imageUrl = $laboDataProduct->getImage();
        if (!$imageUrl) {
            return $this;
        }

        $product_has_images = (bool)Image::getImages($this->getLang(), (int)$product->id);

        $shops = Shop::getShops();

        $image = new Image();
        $image->id_product = $product->id;
        $image->position = Image::getHighestPosition($product->id) + 1;
        $image->cover = (!$product_has_images);
        if (($image->validateFields(false, true)) === true &&
            ($image->validateFieldsLang(false, true)) === true && $image->add()
        ) {
            $image->associateTo($shops);
            if (!LaboDataCopyPaste::copyImg($product->id, $image->id, $imageUrl, 'products', true))
            {
                $image->delete();
            }
        }

        return $this;
    }

    /**
     * Retrouver la TVA
     *
     * @param float $value
     * @return int
     */
    public function getIdTaxRulesGroup($value = null)
    {
        if (!$value) {
            $value = 'standard';
        } else {
            $value = ((float) $value) . '%';
        }
        $groups = TaxRulesGroup::getTaxRulesGroups();
        foreach ($groups as $group) {
            if (false !== stripos($group['name'], $value)) {
                return $group['id_tax_rules_group'];
            }
        }
        foreach ($groups as $group) {
            if ($group['active']) {
                return $group['id_tax_rules_group'];
            }
        }
        return null;
    }
}
