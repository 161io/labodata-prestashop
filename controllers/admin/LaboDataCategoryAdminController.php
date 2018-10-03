<?php
/**
 * LaboData for Prestashop
 *
 * @author 161 SARL <contact@161.io>
 * @copyright (c) 161 SARL, https://161.io
 * @license https://161.io
 */

use LaboDataPrestaShop\Api\Category as LaboDataCategory;
use LaboDataPrestaShop\Api\Tree as LaboDataTree;
use LaboDataPrestaShop\Controller\CategoryResponseJson;
use LaboDataPrestaShop\Controller\StaticAdminController;
use LaboDataPrestaShop\Import\ImportCategory;
use LaboDataPrestaShop\Import\ImportFeature;
use LaboDataPrestaShop\Import\ImportManufacturer;
use LaboDataPrestaShop\Stdlib\ObjectModel;
use ModuleAdminController as NoTabModuleAdminController;

/**
 * @property LaboData $module
 */
class LaboDataCategoryAdminController extends NoTabModuleAdminController
{
    /**
     * @var bool
     * @see LaboDataTreeAdminController
     */
    protected $treeMode = false;

    /**
     * @var string
     */
    protected $typeSelected;

    /**
     * @return LaboDataCategory|LaboDataTree
     */
    protected function getCoreInstance()
    {
        return $this->treeMode ? LaboDataTree::getInstance() : LaboDataCategory::getInstance();
    }

    public function __construct()
    {
        $this->bootstrap = true;

        parent::__construct();

        $this->typeSelected = Tools::getValue('type');
        if (!in_array($this->typeSelected, $this->getCoreInstance()->getCategoryTypeNames())) {
            $this->typeSelected = $this->getCoreInstance()->getDefaultTypeName();
        }

        StaticAdminController::buildHeaderToolbar($this, $this->context);
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);

        //$this->addJS($this->module->getPathUri() . '/views/js/category.js');
        $this->addJS($this->module->getPathUri() . '/views/js/category.min.js');
    }

    public function initContent()
    {
        // Connexion non configuree
        if (!$this->getCoreInstance()->canConnect()) {
            Tools::redirectAdmin($this->context->link->getAdminLink('LaboDataConfigAdmin'));
            return;
        }

        if ($this->isXmlHttpRequest()) {
            return $this->xhrProcess();
        }

        parent::initContent();
    }



    protected function initList()
    {
        $this->fields_list = array(
            'id' => array(
                'title' => $this->module->lc('ID LaboData'),
                'width' => 100,
                'type'  => 'text',
            ),
            'title_fr' => array(
                'title' => $this->module->lc('Titre LaboData'),
                'width' => 140,
                'type'  => 'text',
            ),
            'id_prestashop' => array(
                'title' => $this->module->lc('ID Prestashop'),
                'width' => 100,
                'type'  => 'editable',
            ),
            'title_prestashop' => array(
                'title' => $this->module->lc('Titre Prestashop'),
                'width' => 140,
                'type'  => 'text',
            ),
        );

        // http://doc.prestashop.com/display/PS16/Using+the+HelperList+class
        $helper = new HelperList();
        $helper->simple_header = true;
        $helper->actions = array('add');
        $helper->shopLinkType = '';
        $helper->identifier = 'id';
        //$helper->title = $this->module->lc('Catégories LaboData');
        $helper->token = Tools::getAdminTokenLite($this->controller_name);
        $helper->currentIndex = ModuleAdminController::$currentIndex;

        return $helper;
    }

    /**
     * @param string $token
     * @param string $id
     * @param string $name
     * @return string
     * @see HelperList::displayListContent()
     */
    public function displayAddLink($token, $id, $name = null)
    {
        if ($this->treeMode) {
            $idPrestashop = ImportCategory::getInstance()->getIdCategoryByIdLabodata($id);
            $linkAction = 'addCategory';
        } elseif (LaboDataCategory::TYPE_BRAND == $this->typeSelected) {
            $idPrestashop = ImportManufacturer::getInstance()->getIdManufacturerByIdLabodata($id);
            $linkAction = 'addManufacturer';
        } else {
            $idPrestashop = ImportFeature::getInstance()->getIdFeatureValueByIdLabodata($id);
            $linkAction = 'addFeatureValue';
        }

        $link = $this->context->link->getAdminLink($this->controller_name)
              . '&type=' . $this->typeSelected . '&id=' . $id . '&action=' . $linkAction;

        $smarty = $this->context->smarty;
        /* @var Smarty $tpl */
        $tpl = $smarty->createTemplate($this->getTemplatePath() . 'category-add-link.tpl', $smarty);
        $tpl->assign('link', $link);
        $tpl->assign('disabled', (bool) $idPrestashop);
        return $tpl->fetch();
    }

    /**
     * Menu superieur
     *
     * @return string
     */
    public function renderKpis()
    {
        $smarty = $this->context->smarty;
        $smarty->assign(array(
            'types'         => $this->getCoreInstance()->getCategoryTypes(),
            'type_link'     => $this->context->link->getAdminLink($this->controller_name) . '&type=',
            'type_selected' => $this->typeSelected,
        ));
        return $smarty->fetch($this->getTemplatePath() . 'category-kpis.tpl');
    }

    public function renderList()
    {
        $helper = $this->initList();
        return $helper->generateList(
            $this->getCoreInstance()->getCategoriesByName($this->typeSelected),
            $this->fields_list
        );
    }

    protected function xhrProcess()
    {
        $json = new CategoryResponseJson();
        $json->init();
        $json->type = $this->typeSelected;

        switch ($json->action) {
            // Marque
            case 'addManufacturer':
                $this->actionAddManufacturer($json);
                break;
            case 'bindManufacturer':
                $this->actionBindManufacturer($json);
                break;
            // Caracteristique
            case 'addFeatureValue':
                $this->actionAddFeatureValue($json);
                break;
            case 'bindFeatureValue':
                $this->actionBindFeatureValue($json);
                break;
            // Arborescence
            case 'addCategory':
                $this->actionAddCategory($json);
                break;
            case 'bindCategory':
                $this->actionBindCategory($json);
                break;
        }

        if (ob_get_length()) {
            ob_end_clean();
        }
        header('Content-Type: application/json');
        $this->ajaxDie($json->toString());
    }

    /**
     * Ajouter une marque
     *
     * @param CategoryResponseJson $json
     */
    protected function actionAddManufacturer($json)
    {
        $module = $this->module;
        $json->ldCategory = $this->getCoreInstance()->getCategoryById($json->id);
        $psObject = ImportManufacturer::getInstance()->addManufacturer($json->ldCategory);
        if ($psObject) {
            $json->psIdObject = $psObject->id;
            $json->psNameObject = ObjectModel::getName($psObject);
            $json->growlType = 'notice';
            $json->growlMessage = $module->lc('Marque créée :') . ' ' . ObjectModel::getName($psObject);
        } else {
            $json->growlType = 'error';
            $json->growlMessage = $module->lc('Erreur lors de la création de la marque');
        }
    }

    /**
     * Lier une marque
     *
     * @param CategoryResponseJson $json
     */
    protected function actionBindManufacturer($json)
    {
        $module = $this->module;
        $json->ldCategory = $this->getCoreInstance()->getCategoryById($json->id);
        if (!$json->ldCategory) {
            $json->growlType = 'error';
            $json->growlMessage = $module->lc('Marque introuvable') . ' (LaboData: #' . $json->id . ')';
            return;
        }

        if ($json->deleteIdPrestashop()) {
            ImportManufacturer::getInstance()->deleteManufacturerLabodata($json->ldCategory);
            $json->psIdObject = $json->idPrestashop;

            $json->growlType = 'notice';
            $json->growlMessage = $module->lc('Marque déliée');
        } elseif ($json->idPrestashop > 0) {
            $psObject = new Manufacturer($json->idPrestashop);
            if ($psObject->id) {
                $json->psIdObject = $psObject->id;
                $json->psNameObject = ObjectModel::getName($psObject);

                $json->growlType = 'notice';
                $json->growlMessage = $module->lc('Marque liée :') . ' ' . ObjectModel::getName($psObject);

                ImportManufacturer::getInstance()->addManufacturerLabodata($psObject, $json->ldCategory);
            } else {
                $json->growlType = 'error';
                $json->growlMessage = $module->lc('Marque introuvable')
                                    . ' (Prestashop: #' . $json->idPrestashop . ')';
            }
        } else {
            $json->growlType = 'error';
            $json->growlMessage = $module->lc('Marque introuvable') . ' (Prestashop: #' . $json->idPrestashop . ')';
        }
    }

    /**
     * Ajouter une caracteristique
     *
     * @param CategoryResponseJson $json
     */
    protected function actionAddFeatureValue($json)
    {
        $module = $this->module;
        $json->ldCategory = $this->getCoreInstance()->getCategoryById($json->id);
        $psObject = ImportFeature::getInstance()->addFeatureValue($json->ldCategory);
        if ($psObject) {
            $json->psIdObject = $psObject->id;
            $json->psNameObject = ObjectModel::getName($psObject);
            $json->growlType = 'notice';
            $json->growlMessage = $module->lc('Caractéristique (valeur) :') . ' ' . ObjectModel::getName($psObject);
        } else {
            $json->growlType = 'error';
            $json->growlMessage = $module->lc('Erreur lors de la création de la caractéristique (valeur)');
        }
    }

    /**
     * Lier une caracteristique
     *
     * @param CategoryResponseJson $json
     */
    protected function actionBindFeatureValue($json)
    {
        $module = $this->module;
        $json->ldCategory = $this->getCoreInstance()->getCategoryById($json->id);
        if (!$json->ldCategory) {
            $json->growlType = 'error';
            $json->growlMessage = $module->lc('Caractéristique introuvable') . ' (LaboData: #' . $json->id . ')';
            return;
        }

        if ($json->deleteIdPrestashop()) {
            ImportFeature::getInstance()->deleteFeatureValueLabodata($json->ldCategory);
            $json->psIdObject = $json->idPrestashop;

            $json->growlType = 'notice';
            $json->growlMessage = $module->lc('Caractéristique déliée');
        } elseif ($json->idPrestashop > 0) {
            $psObject = new FeatureValue($json->idPrestashop);
            if ($psObject->id) {
                $json->psIdObject = $psObject->id;
                $json->psNameObject = ObjectModel::getName($psObject);

                $json->growlType = 'notice';
                $json->growlMessage = $module->lc('Caractéristique liée :') . ' ' . ObjectModel::getName($psObject);

                ImportFeature::getInstance()->addFeatureValueLabodata($psObject, $json->ldCategory);
            } else {
                $json->growlType = 'error';
                $json->growlMessage = $module->lc('Caractéristique introuvable')
                                    . ' (Prestashop: #' . $json->idPrestashop . ')';
            }
        } else {
            $json->growlType = 'error';
            $json->growlMessage = $module->lc('Caractéristique introuvable')
                                . ' (Prestashop: #' . $json->idPrestashop . ')';
        }
    }

    /**
     * Ajouter une categorie arborescence
     *
     * @param CategoryResponseJson $json
     */
    protected function actionAddCategory($json)
    {
        $module = $this->module;
        $json->ldCategory = $this->getCoreInstance()->getCategoryById($json->id);
        $psObject = ImportCategory::getInstance()->addCategory($json->ldCategory);
        $psParentObject = new Category($psObject->id_parent);
        if ($psObject) {
            $json->psIdObject = $psObject->id;
            $json->psNameObject = ObjectModel::getName($psObject);
            if ($psParentObject->id) {
                $json->psIdParentObject = $psObject->id_parent;
                $json->psNameParentObject = ObjectModel::getName($psParentObject);
            }
            $json->growlType = 'notice';
            $json->growlMessage = $module->lc('Catégorie créée :') . ' ' . ObjectModel::getName($psObject);
        } else {
            $json->growlType = 'error';
            $json->growlMessage = $module->lc('Erreur lors de la création de la catégorie');
        }
    }

    /**
     * Lier une categorie arborescence
     *
     * @param CategoryResponseJson $json
     */
    protected function actionBindCategory($json)
    {
        $module = $this->module;
        $json->ldCategory = $this->getCoreInstance()->getCategoryById($json->id);
        if (!$json->ldCategory) {
            $json->growlType = 'error';
            $json->growlMessage = $module->lc('Catégorie introuvable') . ' (LaboData: #' . $json->id . ')';
            return;
        }

        if ($json->deleteIdPrestashop()) {
            ImportCategory::getInstance()->deleteCategoryLabodata($json->ldCategory);
            $json->psIdObject = $json->idPrestashop;

            $json->growlType = 'notice';
            $json->growlMessage = $module->lc('Catégorie déliée');
        } elseif ($json->idPrestashop > 0) {
            $psObject = new Category($json->idPrestashop);
            if ($psObject->id) {
                $json->psIdObject = $psObject->id;
                $json->psNameObject = ObjectModel::getName($psObject);

                $json->growlType = 'notice';
                $json->growlMessage = $module->lc('Catégorie liée :') . ' ' . ObjectModel::getName($psObject);

                ImportCategory::getInstance()->addCategoryLabodata($psObject, $json->ldCategory);
            } else {
                $json->growlType = 'error';
                $json->growlMessage = $module->lc('Catégorie introuvable')
                                    . ' (Prestashop: #' . $json->idPrestashop . ')';
            }
        } else {
            $json->growlType = 'error';
            $json->growlMessage = $module->lc('Catégorie introuvable') . ' (Prestashop: #' . $json->idPrestashop . ')';
        }
    }
}
