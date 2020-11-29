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
use Language;
use Manufacturer;
use Product;
use Shop;
use TaxRulesGroup;
use Tools;

/**
 * Injection des elements LaboData vers Prestashop
 *
 * @method static ImportProduct getInstance($renew = false)
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

        // Marque et caracteristique
        $idsPrestashop = $this->convertCategoriesLaboDataToPrestashop($laboDataProduct, false);
        $this->addToFeatureValues($product, $idsPrestashop);

        // Arborescence
        $idsPrestashop = $this->convertCategoriesLaboDataToPrestashop($laboDataProduct, true);
        $product->addToCategories($idsPrestashop);

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
            $productObj = new Product((int) $product['id_product'], true);
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
        $this->hydrateProductLangs($product, $laboDataProduct);
        $this->hydrateProductDatas($product, $laboDataProduct);

        return $this;
    }

    /**
     * @param Product $product
     * @param LaboDataProduct $laboDataProduct
     * @return self
     */
    protected function hydrateProductLangs($product, $laboDataProduct)
    {
        $smarty = Context::getContext()->smarty;
        $tplDir = dirname(__FILE__) . '/../../../views/templates/admin/import-';

        foreach (Language::getIsoIds(false) as $prestaLanguage) {
            $id_lang = $prestaLanguage['id_lang'];
            $iso_code = $prestaLanguage['iso_code'];

            if (empty($product->name[$id_lang])) {
                $product->name[$id_lang] = $laboDataProduct->getTitle($iso_code);
            }
            if (empty($product->meta_title[$id_lang])) {
                $product->meta_title[$id_lang] = $laboDataProduct->getTitle($iso_code);
            }

            if (empty($product->link_rewrite[$id_lang])) {
                $product->link_rewrite[$id_lang] = Tools::link_rewrite($laboDataProduct->getTitle($iso_code));
            }
            if (empty($product->description_short[$id_lang]) && $laboDataProduct->getContent($iso_code)) {
                $smarty->assign(array(
                    'description_short' => $laboDataProduct->getContent($iso_code),
                    'descriptions'      => $laboDataProduct->getAdditionalContent($iso_code),
                ));
                $product->description_short[$id_lang] = trim($smarty->fetch($tplDir . 'description_short.tpl'));
            }
            if (empty($product->description[$id_lang]) && $laboDataProduct->getAdditionalContent($iso_code)) {
                $smarty->assign(array(
                    'description_short' => $laboDataProduct->getContent($iso_code),
                    'descriptions'      => $laboDataProduct->getAdditionalContent($iso_code),
                ));
                $product->description[$id_lang] = trim($smarty->fetch($tplDir . 'description.tpl'));
            }
        }

        return $this;
    }

    /**
     * @param Product $product
     * @param LaboDataProduct $laboDataProduct
     * @return self
     */
    protected function hydrateProductDatas($product, $laboDataProduct)
    {
        // Marque
        if (empty($product->id_manufacturer)) {
            $idManufacturer = ImportManufacturer::getInstance()
                                                ->getIdManufacturerByIdLabodata($laboDataProduct->getBrandId());
            if (!$idManufacturer) {
                foreach ($laboDataProduct->getLangs() as $langCode) {
                    $manufacturerName = $laboDataProduct->getBrandTitle($langCode);
                    $idManufacturer = Manufacturer::getIdByName($manufacturerName);
                    if ($idManufacturer) {
                        break;
                    }
                }
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
     * @param bool $isTree Arborescence
     * @return int[]
     */
    protected function convertCategoriesLaboDataToPrestashop($laboDataProduct, $isTree)
    {
        $laboDataCategory = $isTree ? $laboDataProduct->getTreeIds() : $laboDataProduct->getCategoryIds();
        if (!$laboDataCategory) {
            return array();
        }

        $idsPrestashop = array();
        foreach ($laboDataCategory as $idLabodata) {
            if ($isTree) {
                $idPrestashop = ImportCategory::getInstance()->getIdCategoryByIdLabodata($idLabodata);
            } else {
                $idPrestashop = ImportFeature::getInstance()->getIdFeatureValueByIdLabodata($idLabodata);
            }
            if (!$idPrestashop) {
                continue;
            }
            $idsPrestashop[] = $idPrestashop;
        }

        return $idsPrestashop;
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
