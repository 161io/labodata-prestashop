<?php
/**
 * LaboData for Prestashop
 *
 * @author 161 SARL <contact@161.io>
 * @copyright (c) 161 SARL, https://161.io
 * @license https://161.io
 */

namespace LaboDataPrestaShop\Import;

use Db;
use Configuration;
use Context;
use Feature;
use FeatureValue;
use Image;
use LaboData;
use LaboDataPrestaShop\Api\Product as LaboDataProduct;
use LaboDataPrestaShop\Stdlib\CopyPaste;
use Manufacturer;
use Product;
use Shop;
use TaxRulesGroup;
use Tools;

/**
 * Injection des elements LaboData vers Prestashop
 *
 * @method static ImportProduct getInstance()
 */
class ImportProduct extends AbstractImport
{
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
        $this->hydrateProduct($product, $laboDataProduct);
        $product->save();

        $prestashopIds = $this->convertCategoriesLaboDataToPrestashop($laboDataProduct);
        if ('feature' == LaboData::MODE_CATEGORY) {
            $this->addToFeatureValues($product, $prestashopIds);
        } else {
            $product->addToCategories($prestashopIds);
        }
        if ($laboDataProduct->getBio()) {
            $idFeature = Feature::addFeatureImport('BIO');
            $idFeatureValue = ImportFeature::getInstance()->getFeatureValueIdByName('BIO', $idFeature);
            if (!$idFeatureValue) {
                $idFeatureValue = FeatureValue::addFeatureValueImport($idFeature, 'BIO');
            }
            $product->addFeaturesToDB($idFeature, $idFeatureValue);
        }

        $this->addImage($product, $laboDataProduct);

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
        $product = $this->searchProduct($laboDataProduct);
        if (!$product) {
            return $this->addProduct($laboDataProduct);
        }

        $this->hydrateProduct($product, $laboDataProduct);
        $product->save();

        $this->addImage($product, $laboDataProduct);

        return $product;
    }

    /**
     * @param LaboDataProduct $laboDataProduct
     * @return Product|null
     */
    protected function searchProduct($laboDataProduct)
    {
        $products = Product::searchByName($this->getLang(), $laboDataProduct->getEan13());
        if (!$products) {
            return null;
        }
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
    protected function hydrateProduct($product, $laboDataProduct)
    {
        $smarty = Context::getContext()->smarty;
        $tplDir = dirname(__FILE__) . '/../../../views/templates/admin/import-';

        // Nom du produit
        if (empty($product->name[$this->getLang()])) {
            $product->name = array($this->getLang() => $laboDataProduct->getTitle($this->getLangCode()));
        }
        if (empty($product->meta_title[$this->getLang()])) {
            $product->meta_title = array($this->getLang() => $laboDataProduct->getTitle($this->getLangCode()));
        }

        // Marque
        if (empty($product->id_manufacturer)) {
            $idManufacturer = ImportManufacturer::getInstance()
                                    ->getIdManufacturerByIdLabodata($laboDataProduct->getBrandId());
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
            $product->link_rewrite = array(
                $this->getLang() => Tools::link_rewrite($laboDataProduct->getTitle($this->getLangCode()))
            );
        }
        if (empty($product->description_short) && $laboDataProduct->getContent($this->getLangCode())) {
            $smarty->assign(array(
                'description_short' => $laboDataProduct->getContent($this->getLangCode()),
                'descriptions'      => $laboDataProduct->getAdditionalContent($this->getLangCode()),
            ));
            $product->description_short = trim($smarty->fetch($tplDir . 'description_short.tpl'));
        }
        if (empty($product->description) && $laboDataProduct->getAdditionalContent($this->getLangCode())) {
            $smarty->assign(array(
                'description_short' => $laboDataProduct->getContent($this->getLangCode()),
                'descriptions'      => $laboDataProduct->getAdditionalContent($this->getLangCode()),
            ));
            $product->description = trim($smarty->fetch($tplDir . 'description.tpl'));
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
    protected function convertCategoriesLaboDataToPrestashop($laboDataProduct)
    {
        $laboDataCategory = $laboDataProduct->getCategoryIds();
        if (!$laboDataCategory) {
            return array();
        }

        $prestashopIds = array();
        foreach ($laboDataCategory as $idLabodata) {
            if ('feature' == LaboData::MODE_CATEGORY) {
                $prestashopId = ImportFeature::getInstance()->getIdFeatureValueByIdLabodata($idLabodata);
            } else { // 'category'
                $prestashopId = ImportCategory::getInstance()->getIdCategoryByIdLabodata($idLabodata);
            }
            if (!$prestashopId) {
                continue;
            }
            $prestashopIds[] = $prestashopId;
        }

        return $prestashopIds;
    }

    /**
     * @param Product $product
     * @param int[] $featureValueIds
     * @return bool
     */
    protected function addToFeatureValues($product, $featureValueIds)
    {
        if (empty($featureValueIds)) {
            return false;
        }

        $featureValueIds = array_map('intval', $featureValueIds);
        $sql  = 'SELECT `id_feature_value`, `id_feature` FROM `'._DB_PREFIX_.'feature_value` ';
        $sql .= 'WHERE `id_feature_value` IN (' . implode(', ', $featureValueIds) . ') ';
        $featureValues = Db::getInstance()->executeS($sql);
        if (!$featureValues) {
            return false;
        }

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
    protected function addImage($product, $laboDataProduct)
    {
        $imageUrl = $laboDataProduct->getImage();
        if (!$imageUrl) {
            return $this;
        }

        $product_has_images = (bool) Image::getImages($this->getLang(), (int) $product->id);

        $shops = Shop::getShops();

        $image = new Image();
        $image->id_product = $product->id;
        $image->position = Image::getHighestPosition($product->id) + 1;
        $image->cover = (!$product_has_images);
        if (($image->validateFields(false, true)) === true &&
            ($image->validateFieldsLang(false, true)) === true && $image->add()
        ) {
            $image->associateTo($shops);
            if (!CopyPaste::copyImg($product->id, $image->id, $imageUrl, 'products', true)) {
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
