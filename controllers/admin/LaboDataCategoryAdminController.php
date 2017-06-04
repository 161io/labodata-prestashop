<?php
/**
 * LaboData for Prestashop
 *
 * @author 161 SARL <contact@161.io>
 * @copyright (c) 161 SARL, https://161.io
 * @license https://161.io
 */

use LaboDataPrestaShop\Api\Category as LaboDataCategory;
use LaboDataPrestaShop\Import\ImportCategory;
use LaboDataPrestaShop\Import\ImportFeature;
use LaboDataPrestaShop\Import\ImportManufacturer;
use ModuleAdminController as NoTabModuleAdminController;

/**
 * @property LaboData $module
 */
class LaboDataCategoryAdminController extends NoTabModuleAdminController
{
    /**
     * @var string
     */
    protected $typeSelected;

    public function __construct()
    {
        $this->bootstrap = true;

        parent::__construct();

        $this->typeSelected = Tools::getValue('type');
        if (!in_array($this->typeSelected, LaboDataCategory::getInstance()->getCategoryTypeNames())) {
            $this->typeSelected = LaboDataCategory::TYPE_BRAND;
        }

        $this->buildHeaderToolbar();
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);

        //$this->addJS($this->module->getPathUri() . '/views/js/category.js');
        $this->addJS($this->module->getPathUri() . '/views/js/category.min.js');
    }

    private function buildHeaderToolbar()
    {
        $this->page_header_toolbar_btn['catalog'] = array(
            'href' => $this->context->link->getAdminLink('LaboDataCatalogAdmin'),
            'desc' => $this->module->lc('Catalogue LaboData'),
            'icon' => 'process-icon-new',
        );
        $this->page_header_toolbar_btn['config'] = array(
            'href' => $this->context->link->getAdminLink('LaboDataConfigAdmin'),
            'desc' => $this->module->lc('Configuration'),
            'icon' => 'process-icon-configure',
        );
    }

    public function initContent()
    {
        // Connexion non configuree
        if (!LaboDataCategory::getInstance()->canConnect()) {
            Tools::redirectAdmin($this->context->link->getAdminLink('LaboDataConfigAdmin'));
            return;
        }

        if ($this->isXmlHttpRequest()) {
            return $this->xhrProcess();
        }

        parent::initContent();
    }



    private function initList()
    {
        $this->fields_list = array(
            'id' => array(
                'title' => $this->module->lc('id'),
                'width' => 100,
                'type'  => 'text',
            ),
            'title_fr' => array(
                'title' => $this->module->lc('Titre'),
                'width' => 140,
                'type'  => 'text',
            ),
        );

        // http://doc.prestashop.com/display/PS16/Using+the+HelperList+class
        $helper = new HelperList();
        $helper->simple_header = true;
        $helper->actions = array('add');
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
        if (LaboDataCategory::TYPE_BRAND == $this->typeSelected) {
            $exists = ImportManufacturer::getInstance()->getIdManufacturerByIdLabodata($id);
        } else {
            if ('feature' == LaboData::MODE_CATEGORY) {
                $exists = ImportFeature::getInstance()->getIdFeatureValueByIdLabodata($id);
            } else { // 'category'
                $exists = ImportCategory::getInstance()->getIdCategoryByIdLabodata($id);
            }
        }

        if ($exists) {
            return '<a href="#" disabled="disabled">' ."\n".
                   '<i class="icon-plus"></i> ' . $this->module->lc('Ajouter') . '</a>';
        }

        if (LaboDataCategory::TYPE_BRAND == $this->typeSelected) {
            $action = 'addManufacturer';
        } else {
            $action = 'feature' == LaboData::MODE_CATEGORY ? 'addFeatureValue' : 'addCategory';
        }
        $link = $this->context->link->getAdminLink($this->controller_name)
              . '&type=' . $this->typeSelected . '&id=' . $id . '&action=' . $action;
        return '<a href="#" data-action="' . $link . '">' ."\n".
               '<i class="icon-plus"></i> ' . $this->module->lc('Ajouter') . '</a>';
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
            'types'         => LaboDataCategory::getInstance()->getCategoryTypes(),
            'type_link'     => $this->context->link->getAdminLink($this->controller_name) . '&type=',
            'type_selected' => $this->typeSelected,
        ));

        return $smarty->fetch($this->getTemplatePath() . 'category-kpis.tpl');
    }

    public function renderList()
    {
        $helper = $this->initList();
        return $helper->generateList(
            LaboDataCategory::getInstance()->getCategoriesByName($this->typeSelected),
            $this->fields_list
        );
    }

    private function xhrProcess()
    {
        $action = Tools::substr(Tools::getValue('action'), 0, 20);
        $id = (int) Tools::getValue('id'); // idLabodata (category)

        $json = array(
            'action' => $action,
            'id'     => $id,
            'type'   => $this->typeSelected,
        );

        switch ($action) {
            case 'addManufacturer':
                $json['ldCategory'] = LaboDataCategory::getInstance()->getCategoryById($id);
                $psManufacturer = ImportManufacturer::getInstance()->addManufacturer($json['ldCategory']);
                if ($psManufacturer) {
                    $json['psIdManufacturer'] = $psManufacturer->id;
                    $json['growlType'] = 'notice';
                    $json['growlMessage'] = $this->module->lc('Marque créée :') . ' ' . $psManufacturer->name;
                } else {
                    $json['psIdManufacturer'] = null;
                    $json['growlType'] = 'error';
                    $json['growlMessage'] = $this->module->lc('Erreur lors de la création de la marque');
                }
                break;
            case 'addFeatureValue':
                $json['ldCategory'] = LaboDataCategory::getInstance()->getCategoryById($id);
                $psFeatureValue = ImportFeature::getInstance()->addFeatureValue($json['ldCategory']);
                if ($psFeatureValue) {
                    $json['psIdFeatureValue'] = $psFeatureValue->id;
                    $json['growlType'] = 'notice';
                    $json['growlMessage'] = $this->module->lc('Caractéristique (valeur) :')
                                            . ' ' . $psFeatureValue->value[$this->context->language->id];
                } else {
                    $json['psIdFeatureValue'] = null;
                    $json['growlType'] = 'error';
                    $json['growlMessage'] = $this->module->lc(
                        'Erreur lors de la création de la caractéristique (valeur)'
                    );
                }
                break;
            case 'addCategory':
                $json['ldCategory'] = LaboDataCategory::getInstance()->getCategoryById($id);
                $psCategory = ImportCategory::getInstance()->addCategory($json['ldCategory']);
                if ($psCategory) {
                    $json['psIdCategory'] = $psCategory->id;
                    $json['growlType'] = 'notice';
                    $json['growlMessage'] = $this->module->lc('Catégorie créée :') . ' ' . $psCategory->getName();
                } else {
                    $json['psIdCategory'] = null;
                    $json['growlType'] = 'error';
                    $json['growlMessage'] = $this->module->lc('Erreur lors de la création de la catégorie');
                }
                break;
        }

        ob_end_clean();
        header('Content-Type: application/json');
        $this->ajaxDie(json_encode($json));
    }
}
