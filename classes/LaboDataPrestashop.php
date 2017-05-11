<?php
/**
 * Copyright (c) 161 SARL, https://161.io
 */

use LaboDataPrestaShop\Stdlib\ArrayUtils;

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
     * @var Language
     */
    protected $lang;

    /**
     * @var int[]
     */
    protected $manufacturerLabodataIds;

    /**
     * @var int[]
     */
    protected $featureValueLabodataIds;
    /**
     * @var int[]
     */
    protected $categoryLabodataIds;

    /**
     * @var int[]
     */
    protected $featureIds;

    /**
     * @var int[]
     */
    protected $categoryTypeIds;

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
     * Correspondance entre les marques LaboData et les marques Prestashop
     *
     * @return int[] id_manufacturer
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

            $this->manufacturerLabodataIds = ArrayUtils::arrayColumn($ids, 'id_manufacturer', 'id_labodata');
        }
        return $this->manufacturerLabodataIds;
    }

    /**
     * @param int $idLabodata
     * @return int|null id_manufacturer
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
     * Correspondance entre les categories LaboData et les caracteristiques (valeurs) Prestashop
     *
     * @return int[] id_feature_value
     */
    public function getFeatureValueLabodataIds()
    {
        if (null === $this->featureValueLabodataIds) {
            // Nettoyage
            $sql = 'DELETE FROM `'._DB_PREFIX_.LaboDataCategory::DB_TABLE_FEATURE_VALUE.'` WHERE `id_feature_value` NOT IN (SELECT `id_feature_value` FROM `'._DB_PREFIX_.'feature_value`);';
            Db::getInstance(_PS_USE_SQL_SLAVE_)->execute($sql);

            $sql = new DbQuery();
            $sql->from(LaboDataCategory::DB_TABLE_FEATURE_VALUE);
            $ids = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

            $this->featureValueLabodataIds = ArrayUtils::arrayColumn($ids, 'id_feature_value', 'id_labodata');
        }
        return $this->featureValueLabodataIds;
    }

    /**
     * @param int $idLabodata
     * @return int|null id_feature_value
     */
    public function getIdFeatureValueByIdLabodata($idLabodata)
    {
        $ids = $this->getFeatureValueLabodataIds();
        if (isset($ids[$idLabodata])) {
            return (int) $ids[$idLabodata];
        }
        return null;
    }

    /**
     * Recherche une Feature depuis un name
     *
     * @param string $name
     * @return int|null id_feature
     */
    public function getFeatureIdByName($name)
    {
        $rq = Db::getInstance()->getRow('
			SELECT `id_feature` FROM `'._DB_PREFIX_.'feature_lang`
			WHERE `name` = \''.pSQL($name).'\'
			GROUP BY `id_feature`
		');
        if (empty($rq['id_feature'])) {
            return null;
        }
        return (int) $rq['id_feature'];
    }

    /**
     * Recherche une FeatureValue depuis un name
     *
     * @param string $name
     * @param int $idFeature
     * @return int|null id_feature_value
     */
    public function getFeatureValueIdByName($name, $idFeature = null)
    {
        $idFeature = (int) $idFeature;

        $sql  = 'SELECT `id_feature_value` FROM `'._DB_PREFIX_.'feature_value_lang` ';
        $sql .= 'WHERE `value` = \''.pSQL($name).'\' ';
        if ($idFeature) {
            $sql .= 'AND `id_feature_value` IN ( ';
            $sql .= 'SELECT `id_feature_value` FROM `'._DB_PREFIX_.'feature_value` WHERE `id_feature` = \''.$idFeature.'\' ';
            $sql .= ') ';
        }
        $sql .= 'GROUP BY `id_feature_value`';

        $rq = Db::getInstance()->getRow($sql);
        if (empty($rq['id_feature_value'])) {
            return null;
        }
        return (int) $rq['id_feature_value'];
    }

    /**
     * Retourner l'identifiant du type de caracteristique
     *
     * @param array $laboDataCategoryType
     * @param bool $autoAdd La creer, si elle n'existe pas
     * @return int|null id_feature
     */
    public function getFeatureId($laboDataCategoryType, $autoAdd = false)
    {
        if (!isset($laboDataCategoryType['name'], $laboDataCategoryType['title_fr'])) {
            return null;
        }
        $name = $laboDataCategoryType['name'];
        $title = $laboDataCategoryType['title_fr'];

        if (isset($this->categoryTypeIds[$name])) {
            return $this->categoryTypeIds[$name];
        }

        $idFeature = $this->getFeatureIdByName($title);

        if ($idFeature) {
            $this->featureIds[$name] = (int) $idFeature;
            return $this->featureIds[$name];
        }

        if (!$autoAdd) {
            return null;
        }

        $feature = new Feature();
        $feature->position = Feature::getHigherPosition() + 1;
        $feature->name = LaboDataCopyPaste::createMultiLangField($title);
        $feature->add();

        $this->featureIds[$name] = (int) $feature->id;
        return $this->featureIds[$name];
    }

    /**
     * Ajouter une valeur de caracteristique
     *
     * @param array $laboDataCategory
     * @return FeatureValue|null
     */
    public function addFeatureValue($laboDataCategory)
    {
        if (!isset($laboDataCategory['id'], $laboDataCategory['type'], $laboDataCategory['name'], $laboDataCategory['title_fr'])) {
            return null;
        }

        $featureId = $this->getFeatureId($laboDataCategory['type'], true);
        if (null === $featureId) {
            return null;
        }

        //$name = $laboDataCategory['name'];
        $title = $laboDataCategory['title_fr'];

        $featureValue = new FeatureValue();
        $featureValue->id_feature = $featureId;
        $featureValue->value = LaboDataCopyPaste::createMultiLangField($title);
        $featureValue->add();

        $this->_addFeatureValueLabodata($featureValue, $laboDataCategory);

        return $featureValue;
    }

    /**
     * Jointure entre ids LaboData et Prestashop
     *
     * @param FeatureValue $featureValue
     * @param array $laboDataCategory
     * @return bool
     */
    protected function _addFeatureValueLabodata($featureValue, $laboDataCategory)
    {
        return Db::getInstance()->insert(LaboDataCategory::DB_TABLE_FEATURE_VALUE, array(
            'id_feature_value' => (int) $featureValue->id,
            'id_labodata'      => (int) $laboDataCategory['id'],
        ));
    }



    /**
     * Correspondance entre les categories LaboData et les categories Prestashop
     *
     * @return int[] id_category
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

            $this->categoryLabodataIds = ArrayUtils::arrayColumn($ids, 'id_category', 'id_labodata');
        }
        return $this->categoryLabodataIds;
    }

    /**
     * @param int $idLabodata
     * @return int|null id_category
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
     * Retourner l'identifiant de la categorie type
     *
     * @param array $laboDataCategoryType
     * @param bool $autoAdd La creer, si elle n'existe pas
     * @return int|null id_category
     */
    public function getCategoryTypeId($laboDataCategoryType, $autoAdd = false)
    {
        if (!isset($laboDataCategoryType['name'], $laboDataCategoryType['title_fr'])) {
            return null;
        }
        $name = $laboDataCategoryType['name'];
        $title = $laboDataCategoryType['title_fr'];

        if (isset($this->categoryTypeIds[$name])) {
            return $this->categoryTypeIds[$name];
        }

        //$idParentCategory = Configuration::get('PS_ROOT_CATEGORY');
        $idParentCategory = Configuration::get('PS_HOME_CATEGORY');
        $category = Category::searchByNameAndParentCategoryId($this->getLang(), $title, $idParentCategory);

        if ($category) {
            $this->categoryTypeIds[$name] = (int) $category['id_category'];
            return $this->categoryTypeIds[$name];
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

        $this->categoryTypeIds[$name] = (int) $category->id;
        return $this->categoryTypeIds[$name];
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

        $categoryTypeId = $this->getCategoryTypeId($laboDataCategory['type'], true);
        if (null === $categoryTypeId) {
            return null;
        }

        $name = $laboDataCategory['name'];
        $title = $laboDataCategory['title_fr'];

        $category = new Category();
        $category->is_root_category = false;
        $category->id_parent = $categoryTypeId;
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

        $prestashopIds = $this->_convertCategoriesLaboDataToPrestashop($laboDataProduct);
        if ('feature' == LaboData::MODE_CATEGORY) {
            $this->_product_addToFeatureValues($product, $prestashopIds);
        } else {
            $product->addToCategories($prestashopIds);
        }
        if ($laboDataProduct->getBio()) {
            $idFeature = Feature::addFeatureImport('BIO');
            $idFeatureValue = $this->getFeatureValueIdByName('BIO', $idFeature);
            if (!$idFeatureValue) {
                $idFeatureValue = FeatureValue::addFeatureValueImport($idFeature, 'BIO');
            }
            $product->addFeaturesToDB($idFeature, $idFeatureValue);
        }

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

        $prestashopIds = array();
        foreach ($laboDataCategory as $idLabodata) {
            if ('feature' == LaboData::MODE_CATEGORY) {
                $prestashopId = $this->getIdFeatureValueByIdLabodata($idLabodata);
            } else { // 'category'
                $prestashopId = $this->getIdCategoryByIdLabodata($idLabodata);
            }
            if (!$prestashopId) { continue; }
            $prestashopIds[] = $prestashopId;
        }

        return $prestashopIds;
    }

    /**
     * @param Product $product
     * @param int[] $featureValueIds
     * @return bool
     */
    protected function _product_addToFeatureValues($product, $featureValueIds)
    {
        if (empty($featureValueIds)) { return false; }

        $featureValueIds = array_map('intval', $featureValueIds);
        $sql  = 'SELECT `id_feature_value`, `id_feature` FROM `'._DB_PREFIX_.'feature_value` ';
        $sql .= 'WHERE `id_feature_value` IN (' . implode(', ', $featureValueIds) . ')';
        $featureValues = Db::getInstance()->executeS($sql);
        if (!$featureValues) { return false; }

        foreach ($featureValues as $featureValue) {
            Db::getInstance()->insert('feature_product', array(
                'id_feature'       => (int) $featureValue['id_feature'],
                'id_product'       => (int) $product->id,
                'id_feature_value' => (int) $featureValue['id_feature_value'],
            ));
        }

        return true;
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
