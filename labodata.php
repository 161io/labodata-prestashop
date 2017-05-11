<?php
/**
 * Copyright (c) 161 SARL, https://161.io
 */

//error_reporting(E_ALL); ini_set('display_errors', '1');
if (!defined('_PS_VERSION_')) {
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';

/**
 * Dev.mode: config/defines.inc.php _PS_MODE_DEV_
 *
 * @link http://developers.prestashop.com/module/05-CreatingAPrestaShop17Module/index.html
 * @link http://build.prestashop.com/prestashop-ui-kit/
 * @link https://www.prestasoo.com/fr/Blog/prestashop-1-6-icons-list.html
 */
class LaboData extends Module
{
    /**
     * @const string Type d'importation des categories LaboData ( category ou feature )
     */
    //const MODE_CATEGORY = 'category'; // 0.2.0
    const MODE_CATEGORY = 'feature'; // 0.3.0

    /**
     * @var AppKernel
     */
    protected $kernel;

    public function __construct()
    {
        $this->name = 'labodata';
        $this->tab = 'others';
        $this->version = '0.3.0';
        $this->author = '161 SARL';

        // 0 = Front // 1 = Back-office
        $this->need_instance = 1;

        $this->ps_versions_compliancy = array('min' => '1.6.1.0', 'max' => _PS_VERSION_);
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('LaboData');
        $this->description = $this->l('Constituez le catalogue de votre pharmacie ou parapharmacie en ligne avec LaboData avec +12000 fiches produits avec photos et descriptifs. Menu : "Catalogue" >> "LaboData"');
        $this->confirmUninstall = $this->l('Êtes-vous sûr de vouloir désinstaller le module ?');

        if (!Configuration::get('LABODATA_NAME')) {
            $this->warning = $this->l('Aucun module portant ce nom n\'a été trouvé');
        }
    }

    /**
     * @return AppKernel
     * @since Prestashop 1.7
     */
    public function getKernel()
    {
        if (null === $this->kernel) {
            global $kernel;
            $this->kernel = !empty($kernel) && $kernel instanceof AppKernel ? $kernel : false;
        }
        return $this->kernel;
    }

    public function install()
    {
        $this->_clearCache('*');
        if (!parent::install()
            || !$this->_installConfig()
        ) {
            return false;
        }

        $this->_installTabs();
        $this->_installTables();

        return true;
    }

    public function uninstall()
    {
        $this->_clearCache('*');
        if (!parent::uninstall() || !$this->_uninstallConfig()) {
            return false;
        }

        $this->_uninstallTabs();
        //$this->_uninstallTables();
        LaboDataCategory::getInstance()->deleteCache();

        return true;
    }

    public function _installConfig()
    {
        if (!Configuration::updateValue(LaboDataQuery::CONF_EMAIL, '') || !Configuration::updateValue(LaboDataQuery::CONF_SECRET_KEY, '')) {
            return false;
        }
        return true;
    }

    public function _uninstallConfig()
    {
        if (!Configuration::deleteByName(LaboDataQuery::CONF_EMAIL) || !Configuration::deleteByName(LaboDataQuery::CONF_SECRET_KEY)) {
            return false;
        }
        return true;
    }

    public function _installTabs()
    {
        // "Catalogue" >> "LaboData"
        $languages = Language::getLanguages(true);
        $catalogIdParent = null;
        if ($this->getKernel()) {
            $tabServiceId = 'prestashop.core.admin.tab.repository';
            if ($this->getKernel()->getContainer()->has($tabServiceId)) {
                /* @var \PrestaShopBundle\Entity\Repository\TabRepository $tabRepository */
                $tabRepository = $this->getKernel()->getContainer()->get($tabServiceId);
                $catalogIdParent = $tabRepository->findOneIdByClassName('AdminCatalog');
            }
        }
        if (null === $catalogIdParent) {
            $catalogIdParent = Tab::getIdFromClassName('AdminCatalog');
        }

        $parentTab = new Tab();
        $parentTab->active = true;
        $parentTab->name = array();
        foreach ($languages as $lang) {
            $parentTab->name[$lang['id_lang']] = $this->l('LaboData');
        }
        $parentTab->class_name = 'LaboDataCatalogAdmin';
        $parentTab->id_parent = $catalogIdParent;
        $parentTab->module = $this->name;
        $parentTab->add();

        if (!version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
            // "LaboData"
            $parentTab = new Tab();
            $parentTab->active = true;
            $parentTab->name = array();
            foreach ($languages as $lang) {
                $parentTab->name[$lang['id_lang']] = $this->l('LaboData');
            }
            $parentTab->class_name = 'LaboDataCatalogAdmin';
            $parentTab->id_parent = 0;
            $parentTab->module = $this->name;
            $parentTab->add();
        }

        // Recherche dans LaboData
        $catalogTab = new Tab();
        $catalogTab->active = true;
        $catalogTab->name = array();
        foreach ($languages as $lang) {
            $catalogTab->name[$lang['id_lang']] = $this->l('Catalogue LaboData');
        }
        $catalogTab->class_name = 'LaboDataCatalogAdmin';
        $catalogTab->id_parent = $parentTab->id;
        $catalogTab->module = $this->name;
        $catalogTab->add();

        // Categories
        $categoryTab = new Tab();
        $categoryTab->active = true;
        $categoryTab->name = array();
        foreach ($languages as $lang) {
            $categoryTab->name[$lang['id_lang']] = $this->l('Marques/Caractéristiques');
        }
        $categoryTab->class_name = 'LaboDataCategoryAdmin';
        $categoryTab->id_parent = $parentTab->id;
        $categoryTab->module = $this->name;
        $categoryTab->add();

        // Configuration
        $configTab = new Tab();
        $configTab->active = true;
        $configTab->name = array();
        foreach ($languages as $lang) {
            $configTab->name[$lang['id_lang']] = $this->l('Configuration');
        }
        $configTab->class_name = 'LaboDataConfigAdmin';
        $configTab->id_parent = $parentTab->id;
        $configTab->module = $this->name;
        $configTab->add();
    }

    public function _uninstallTabs()
    {
        $tabs = Tab::getCollectionFromModule($this->name);
        if (empty($tabs)) { return true; }
        foreach ($tabs as $tab) {
            $tab->delete();
        }

        return true;
    }

    public function _installTables()
    {
        Db::getInstance()->execute(
'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.LaboDataCategory::DB_TABLE_MANUFACTURER.'` (
  `id_manufacturer` INT(10) UNSIGNED NOT NULL,
  `id_labodata` INT(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id_manufacturer`, `id_labodata`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;'
        );

        Db::getInstance()->execute(
'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.LaboDataCategory::DB_TABLE_FEATURE_VALUE.'` (
  `id_feature_value` INT(10) UNSIGNED NOT NULL,
  `id_labodata` INT(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id_feature_value`, `id_labodata`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;'
        );

        Db::getInstance()->execute(
'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.LaboDataCategory::DB_TABLE_CATEGORY.'` (
  `id_category` INT(10) UNSIGNED NOT NULL,
  `id_labodata` INT(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id_category`, `id_labodata`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;'
        );

        return true;
    }

    public function _uninstallTables()
    {
        Db::getInstance()->execute('DROP TABLE `'._DB_PREFIX_.LaboDataCategory::DB_TABLE_MANUFACTURER.'`;');
        Db::getInstance()->execute('DROP TABLE `'._DB_PREFIX_.LaboDataCategory::DB_TABLE_FEATURE_VALUE.'`;');
        Db::getInstance()->execute('DROP TABLE `'._DB_PREFIX_.LaboDataCategory::DB_TABLE_CATEGORY.'`;');

        return true;
    }

    /**
     * Configuration
     *
     * @return string
     */
    public function getContent()
    {
        Tools::redirectAdmin($this->context->link->getAdminLink('LaboDataConfigAdmin'));
        return '';
    }
}
