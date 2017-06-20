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
use DbQuery;
use LaboDataPrestaShop\Api\Category as LaboDataCategory;
use LaboDataPrestaShop\Stdlib\ArrayUtils;
use LaboDataPrestaShop\Stdlib\CopyPaste;
use Manufacturer;
use Tools;

/**
 * Injection des elements LaboData vers Prestashop
 *
 * @method static ImportManufacturer getInstance()
 */
class ImportManufacturer extends AbstractImport
{
    /**
     * @var string
     */
    protected $idColumn = 'id_manufacturer';

    /**
     * @var int[]
     */
    protected $manufacturerLabodataIds;

    /**
     * Correspondance entre les marques LaboData et les marques Prestashop
     *
     * @return int[] id_manufacturer
     */
    public function getManufacturerLabodataIds()
    {
        if (null === $this->manufacturerLabodataIds) {
            // Nettoyage ( $this->idColumn )
            $sql  = 'DELETE FROM `'._DB_PREFIX_.LaboDataCategory::DB_TABLE_MANUFACTURER.'` ';
            $sql .= 'WHERE `id_manufacturer` NOT IN ( ';
            $sql .=   'SELECT `id_manufacturer` FROM `'._DB_PREFIX_.'manufacturer` ';
            $sql .= ') ';
            Db::getInstance(_PS_USE_SQL_SLAVE_)->execute($sql);

            $sql = new DbQuery();
            $sql->from(LaboDataCategory::DB_TABLE_MANUFACTURER);
            $ids = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

            $this->manufacturerLabodataIds = ArrayUtils::arrayColumn($ids, $this->idColumn, $this->labodataColumn);
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
        if (!isset(
            $laboDataCategory['id'],
            $laboDataCategory['type'],
            $laboDataCategory['name'],
            $laboDataCategory['title_fr']
        )) {
            return null;
        }

        $name = $laboDataCategory['name'];
        $title = $laboDataCategory['title_fr'];

        $manufacturer = new Manufacturer();
        $manufacturer->active = true;
        $manufacturer->name = $title;
        $manufacturer->meta_title = CopyPaste::createMultiLangField($title);
        $manufacturer->meta_description = CopyPaste::createMultiLangField($title);
        $manufacturer->link_rewrite = CopyPaste::createMultiLangField(Tools::link_rewrite($name));
        $manufacturer->add();

        $this->addManufacturerLabodata($manufacturer, $laboDataCategory);

        return $manufacturer;
    }

    /**
     * Jointure entre ids LaboData et Prestashop
     *
     * @param Manufacturer $manufacturer
     * @param array $laboDataCategory
     * @return bool
     */
    protected function addManufacturerLabodata($manufacturer, $laboDataCategory)
    {
        return Db::getInstance()->insert(LaboDataCategory::DB_TABLE_MANUFACTURER, array(
            $this->idColumn       => (int) $manufacturer->id,
            $this->labodataColumn => (int) $laboDataCategory['id'],
        ));
    }
}
