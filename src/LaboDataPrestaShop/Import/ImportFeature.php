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
 * @method static ImportFeature getInstance()
 */
class ImportFeature extends AbstractImport
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
    protected $featureValueLabodataIds;

    /**
     * @var int[]
     */
    protected $featureIds;

    /**
     * Correspondance entre les categories LaboData et les caracteristiques (valeurs) Prestashop
     *
     * @return int[] id_feature_value
     */
    public function getFeatureValueLabodataIds()
    {
        if (null === $this->featureValueLabodataIds) {
            // Nettoyage
            $sql = 'DELETE FROM `'._DB_PREFIX_.LaboDataCategory::DB_TABLE_FEATURE_VALUE.'` ';
            $sql .= 'WHERE `'.$this->idColumn2.'` NOT IN (SELECT `'.$this->idColumn2.'` FROM `'._DB_PREFIX_.'feature_value`)';
            Db::getInstance(_PS_USE_SQL_SLAVE_)->execute($sql);

            $sql = new DbQuery();
            $sql->from(LaboDataCategory::DB_TABLE_FEATURE_VALUE);
            $ids = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

            $this->featureValueLabodataIds = ArrayUtils::arrayColumn($ids, $this->idColumn2, $this->labodataColumn);
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
            $sql .= 'SELECT `id_feature_value` FROM `'._DB_PREFIX_.'feature_value` ';
            $sql .= 'WHERE `id_feature` = \''.$idFeature.'\' ';
            $sql .= ') ';
        }
        $sql .= 'GROUP BY `id_feature_value`';

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
        if (!isset($laboDataCategoryType['name'], $laboDataCategoryType['title_fr'])) {
            return null;
        }
        $name = $laboDataCategoryType['name'];
        $title = $laboDataCategoryType['title_fr'];

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
        $feature->name = CopyPaste::createMultiLangField($title);
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
            $laboDataCategory['title_fr']
        )) {
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
        $featureValue->value = CopyPaste::createMultiLangField($title);
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
    protected function addFeatureValueLabodata($featureValue, $laboDataCategory)
    {
        return Db::getInstance()->insert(LaboDataCategory::DB_TABLE_FEATURE_VALUE, array(
            $this->idColumn2      => (int) $featureValue->id,
            $this->labodataColumn => (int) $laboDataCategory['id'],
        ));
    }
}
