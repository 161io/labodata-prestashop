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
use Db;
use LaboData;
use LaboDataPrestaShop\Api\Category;
use LaboDataPrestaShop\Api\Query;
use LaboDataPrestaShop\Stdlib\Cache;
use Tab;

class Uninstall
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
        if (!Configuration::deleteByName(Query::CONF_EMAIL) || !Configuration::deleteByName(Query::CONF_SECRET_KEY)) {
            return false;
        }
        return true;
    }

    public function tab()
    {
        $tabs = Tab::getCollectionFromModule($this->getModule()->name);
        if (empty($tabs)) {
            return true;
        }
        foreach ($tabs as $tab) {
            $tab->delete();
        }

        return true;
    }

    public function table()
    {
        Db::getInstance()->execute('DROP TABLE `'._DB_PREFIX_.Category::DB_TABLE_MANUFACTURER.'`;');
        Db::getInstance()->execute('DROP TABLE `'._DB_PREFIX_.Category::DB_TABLE_FEATURE_VALUE.'`;');
        Db::getInstance()->execute('DROP TABLE `'._DB_PREFIX_.Category::DB_TABLE_CATEGORY.'`;');

        return true;
    }

    public function cache()
    {
        Cache::clear();

        return true;
    }
}
