<?php
/**
 * LaboData for Prestashop
 *
 * @author 161 SARL <contact@161.io>
 * @copyright (c) 161 SARL, https://161.io
 * @license https://161.io
 */

namespace LaboDataPrestaShop\Install;

use Configuration;
use Context;
use Db;
use LaboData;
use LaboDataPrestaShop\Api\Category;
use LaboDataPrestaShop\Api\Query;
use Language;
use Tab;

class Install
{
    /**
     * @var LaboData
     */
    protected $module;

    /**
     * @param LaboData $module
     */
    public function __construct($module)
    {
        $this->module = $module;
    }

    /**
     * @return LaboData
     */
    public function getModule()
    {
        return $this->module;
    }

    public function configuration()
    {
        if (!Configuration::updateValue(Query::CONF_EMAIL, '')
            || !Configuration::updateValue(Query::CONF_SECRET_KEY, '')
        ) {
            return false;
        }
        return true;
    }

    public function tab()
    {
        $module = $this->getModule();

        //$languages = array( Context::getContext()->language->id );
        $languages = Language::getLanguages(true, false, true);

        // "Catalogue" >> "LaboData"
        $catalogIdParent = null;
        if ($module->getKernel()) {
            $tabServiceId = 'prestashop.core.admin.tab.repository';
            if ($module->getKernel()->getContainer()->has($tabServiceId)) {
                /* @var \PrestaShopBundle\Entity\Repository\TabRepository $tabRepository */
                $tabRepository = $module->getKernel()->getContainer()->get($tabServiceId);
                $catalogIdParent = $tabRepository->findOneIdByClassName('AdminCatalog');
            }
        }
        if (null === $catalogIdParent) {
            $catalogIdParent = Tab::getIdFromClassName('AdminCatalog');
        }

        $parentTab = new Tab();
        $parentTab->active = true;
        $parentTab->name = array();
        foreach ($languages as $id_lang) {
            $parentTab->name[$id_lang] = $module->displayName;
        }
        $parentTab->class_name = 'LaboDataCatalogAdmin';
        $parentTab->id_parent = $catalogIdParent;
        $parentTab->module = $module->name;
        $parentTab->add();

        if (!version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
            // "LaboData"
            $parentTab = new Tab();
            $parentTab->active = true;
            $parentTab->name = array();
            foreach ($languages as $id_lang) {
                $parentTab->name[$id_lang] = $module->displayName;
            }
            $parentTab->class_name = 'LaboDataCatalogAdmin';
            $parentTab->id_parent = 0;
            $parentTab->module = $module->name;
            $parentTab->add();
        }

        // Recherche dans LaboData
        $catalogTab = new Tab();
        $catalogTab->active = true;
        $catalogTab->name = array();
        foreach ($languages as $id_lang) {
            $catalogTab->name[$id_lang] = $module->lc('Catalogue LaboData');
        }
        $catalogTab->class_name = 'LaboDataCatalogAdmin';
        $catalogTab->id_parent = $parentTab->id;
        $catalogTab->module = $module->name;
        $catalogTab->add();

        // Categories
        $categoryTab = new Tab();
        $categoryTab->active = true;
        $categoryTab->name = array();
        foreach ($languages as $id_lang) {
            $categoryTab->name[$id_lang] = $module->lc('Marques/Caractéristiques');
        }
        $categoryTab->class_name = 'LaboDataCategoryAdmin';
        $categoryTab->id_parent = $parentTab->id;
        $categoryTab->module = $module->name;
        $categoryTab->add();

        // Configuration
        $configTab = new Tab();
        $configTab->active = true;
        $configTab->name = array();
        foreach ($languages as $id_lang) {
            $configTab->name[$id_lang] = $module->lc('Configuration');
        }
        $configTab->class_name = 'LaboDataConfigAdmin';
        $configTab->id_parent = $parentTab->id;
        $configTab->module = $module->name;
        $configTab->add();

        return true;
    }

    public function table()
    {
        Db::getInstance()->execute(
'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.Category::DB_TABLE_MANUFACTURER.'` (
  `id_manufacturer` INT(10) UNSIGNED NOT NULL,
  `id_labodata` INT(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id_manufacturer`, `id_labodata`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;'
        );

        Db::getInstance()->execute(
'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.Category::DB_TABLE_FEATURE_VALUE.'` (
  `id_feature_value` INT(10) UNSIGNED NOT NULL,
  `id_labodata` INT(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id_feature_value`, `id_labodata`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;'
        );

        Db::getInstance()->execute(
'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.Category::DB_TABLE_CATEGORY.'` (
  `id_category` INT(10) UNSIGNED NOT NULL,
  `id_labodata` INT(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id_category`, `id_labodata`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;'
        );

        return true;
    }
}
