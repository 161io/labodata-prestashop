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
use Feature;
use FeatureValue;
use LaboDataPrestaShop\Api\Category as LaboDataCategory;
use LaboDataPrestaShop\Stdlib\ArrayUtils;
use LaboDataPrestaShop\Stdlib\CopyPaste;

/**
 * Injection des elements LaboData vers Prestashop
 *
 * @method static ImportFeature getInstance($renew = false)
 */
class ImportFeature extends AbstractImportCategory
{
    /**
     * @var string
     */
    protected $idColumn1 = 'id_feature';

    /**
     * @var string
     */
    protected $idColumn2 = 'id_feature_value';

    /**
     * @var int[]
     */
    protected $featureIds;

    /**
     * @return string
     */
    public function getTable()
    {
        return LaboDataCategory::DB_TABLE_FEATURE_VALUE;
    }

    /**
     * Correspondance entre les categories LaboData et les caracteristiques (valeurs) Prestashop
     *
     * @param bool $purgeIds
     * @return int[] id_feature_value
     */
    public function getFeatureValueLabodataIds($purgeIds = true)
    {
        if (null === $this->dataLabodataIds) {
            // Nettoyage ( $this->idColumn2 )
            if ($purgeIds) {
                $sql  = 'DELETE FROM `'._DB_PREFIX_.$this->getTable().'` ';
                $sql .= 'WHERE `id_feature_value` NOT IN (';
                $sql .=   'SELECT `id_feature_value` FROM `'._DB_PREFIX_.'feature_value` ';
                $sql .= ')';
                Db::getInstance(_PS_USE_SQL_SLAVE_)->execute($sql);
            }

            $sql = new DbQuery();
            $sql->from($this->getTable());
            $ids = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

            $this->dataLabodataIds = ArrayUtils::arrayColumn($ids, $this->idColumn2, $this->labodataColumn);
        }
        return $this->dataLabodataIds;
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
        $sql  = 'SELECT `id_feature` FROM `'._DB_PREFIX_.'feature_lang` ';
        $sql .= 'WHERE `name` = \''.pSQL($name).'\' ';
        $sql .= 'GROUP BY `id_feature` ';
        $rq = Db::getInstance()->getRow($sql);
        if (empty($rq[$this->idColumn1])) {
            return null;
        }
        return (int) $rq[$this->idColumn1];
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
            $sql .=   'SELECT `id_feature_value` FROM `'._DB_PREFIX_.'feature_value` ';
            $sql .=   'WHERE `id_feature` = \''.pSQL($idFeature).'\' ';
            $sql .= ') ';
        }
        $sql .= 'GROUP BY `id_feature_value` ';

        $rq = Db::getInstance()->getRow($sql);
        if (empty($rq[$this->idColumn2])) {
            return null;
        }
        return (int) $rq[$this->idColumn2];
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
        if (!isset($laboDataCategoryType['name'], $laboDataCategoryType['title'])) {
            return null;
        }
        $name = $laboDataCategoryType['name'];
        $title = $laboDataCategoryType['title'];

        if (isset($this->featureIds[$name])) {
            return $this->featureIds[$name];
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
        $feature->name = CopyPaste::createMultiLangFieldByItem($laboDataCategoryType);
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
        if (!isset(
            $laboDataCategory['id'],
            $laboDataCategory['type'],
            $laboDataCategory['name'],
            $laboDataCategory['title']
        )) {
            return null;
        }

        $featureId = $this->getFeatureId($laboDataCategory['type'], true);
        if (null === $featureId) {
            return null;
        }

        $featureValue = new FeatureValue();
        $featureValue->id_feature = $featureId;
        $featureValue->value = CopyPaste::createMultiLangFieldByItem($laboDataCategory);
        $featureValue->add();

        $this->addFeatureValueLabodata($featureValue, $laboDataCategory);

        return $featureValue;
    }

    /**
     * Jointure entre ids LaboData et Prestashop
     *
     * @param FeatureValue $featureValue
     * @param array $laboDataCategory
     * @return bool
     */
    public function addFeatureValueLabodata($featureValue, $laboDataCategory)
    {
        return Db::getInstance()->insert($this->getTable(), array(
            $this->idColumn2      => (int) $featureValue->id,
            $this->labodataColumn => (int) $laboDataCategory['id'],
        ));
    }

    /**
     * Retirer le lien entre ids LaboData et Prestashop
     *
     * @param array $laboDataCategory
     * @return bool
     */
    public function deleteFeatureValueLabodata($laboDataCategory)
    {
        return Db::getInstance()->delete(
            $this->getTable(),
            'id_labodata = ' . (int) $laboDataCategory['id']
        );
    }
}
