<?php
/**
 * LaboData for Prestashop
 *
 * @author 161 SARL <contact@161.io>
 * @copyright (c) 161 SARL, https://161.io
 * @license https://161.io
 */

namespace LaboDataPrestaShop\Import;

use Category;
use Configuration;
use Db;
use DbQuery;
use LaboDataPrestaShop\Api\Category as LaboDataCategory;
use LaboDataPrestaShop\Api\Tree as LaboDataTree;
use LaboDataPrestaShop\Stdlib\ArrayUtils;
use LaboDataPrestaShop\Stdlib\CopyPaste;
use Tools;

/**
 * Injection des elements LaboData vers Prestashop
 *
 * @method static ImportCategory getInstance($renew = false)
 */
class ImportCategory extends AbstractImportCategory
{
    /**
     * @var string
     */
    protected $idColumn = 'id_category';

    /**
     * @var int[]
     */
    protected $categoryTypeIds;

    /**
     * @return string
     */
    public function getTable()
    {
        return LaboDataCategory::DB_TABLE_CATEGORY;
    }

    /**
     * Correspondance entre les categories LaboData et les categories Prestashop
     *
     * @param bool $purgeIds
     * @return int[] id_category
     */
    public function getCategoryLabodataIds($purgeIds = true)
    {
        if (null === $this->dataLabodataIds) {
            // Nettoyage ( $this->idColumn )
            if ($purgeIds) {
                $sql  = 'DELETE FROM `'._DB_PREFIX_.$this->getTable().'` ';
                $sql .= 'WHERE `id_category` NOT IN (';
                $sql .=   'SELECT `id_category` FROM `'._DB_PREFIX_.'category` ';
                $sql .= ')';
                Db::getInstance(_PS_USE_SQL_SLAVE_)->execute($sql);
            }

            $sql = new DbQuery();
            $sql->from($this->getTable());
            $ids = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

            $this->dataLabodataIds = ArrayUtils::arrayColumn($ids, $this->idColumn, $this->labodataColumn);
        }
        return $this->dataLabodataIds;
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
            $this->categoryTypeIds[$name] = (int) $category[$this->idColumn];
            return $this->categoryTypeIds[$name];
        }

        if (!$autoAdd) {
            return null;
        }

        $category = new Category();
        $category->is_root_category = false;
        $category->id_parent = $idParentCategory;
        $category->active = true;
        $category->name = CopyPaste::createMultiLangField($title);
        $category->meta_title = CopyPaste::createMultiLangField($title);
        $category->meta_description = CopyPaste::createMultiLangField($title);
        $category->link_rewrite = CopyPaste::createMultiLangField(Tools::link_rewrite($name));
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
        if (!isset(
            $laboDataCategory['id'],
            $laboDataCategory['type'],
            $laboDataCategory['name'],
            $laboDataCategory['title_fr']
        )) {
            return null;
        }

        $categoryTypeId = $this->getCategoryTypeId($laboDataCategory['type'], true);
        if (null === $categoryTypeId) {
            return null;
        }

        if (isset($laboDataCategory['parent_id'])) {
            $findCategory = $this->getIdCategoryByIdLabodata($laboDataCategory['parent_id']);
            if (!$findCategory) {
                $parent = LaboDataTree::getInstance()->getCategoryById($laboDataCategory['parent_id']);
                $newCategory = $this->addCategory($parent);
                $categoryTypeId = $newCategory->id;
            } else {
                $categoryTypeId = $findCategory;
            }
        }

        $name = $laboDataCategory['name'];
        $title = $laboDataCategory['title_fr'];

        $category = new Category();
        $category->is_root_category = false;
        $category->id_parent = $categoryTypeId;
        $category->active = true;
        $category->name = CopyPaste::createMultiLangField($title);
        $category->meta_title = CopyPaste::createMultiLangField($title);
        $category->meta_description = CopyPaste::createMultiLangField($title);
        $category->link_rewrite = CopyPaste::createMultiLangField(Tools::link_rewrite($name));
        $category->add();

        $this->addCategoryLabodata($category, $laboDataCategory);

        return $category;
    }

    /**
     * Jointure entre ids LaboData et Prestashop
     *
     * @param Category $category
     * @param array $laboDataCategory
     * @return bool
     */
    public function addCategoryLabodata($category, $laboDataCategory)
    {
        return Db::getInstance()->insert($this->getTable(), array(
            $this->idColumn       => (int) $category->id,
            $this->labodataColumn => (int) $laboDataCategory['id'],
        ));
    }

    /**
     * Retirer le lien entre ids LaboData et Prestashop
     *
     * @param array $laboDataCategory
     * @return bool
     */
    public function deleteCategoryLabodata($laboDataCategory)
    {
        return Db::getInstance()->delete(
            $this->getTable(),
            'id_labodata = ' . (int) $laboDataCategory['id']
        );
    }
}
