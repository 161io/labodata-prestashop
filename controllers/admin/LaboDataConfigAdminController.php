<?php
/**
 * Copyright (c) 161 SARL, https://161.io
 */

use LaboDataPrestaShop\Api\Account;
use LaboDataPrestaShop\Api\Category as LaboDataCategory;
use LaboDataPrestaShop\Api\Query as LaboDataQuery;
use ModuleAdminController as NoTabModuleAdminController;

/**
 * Configuration de LaboData
 *
 * @property LaboData $module
 */
class LaboDataConfigAdminController extends NoTabModuleAdminController
{
    protected $messageError = false;
    protected $messageContent = '';

    public function __construct()
    {
        $this->bootstrap = true;

        parent::__construct();

        if (LaboDataCategory::getInstance()->canConnect()) {
            $this->buildHeaderToolbar();
        }
    }

    public function setMedia()
    {
        parent::setMedia();

        //$this->addJS($this->module->getPathUri() . '/views/js/config.js');
        $this->addJS($this->module->getPathUri() . '/views/js/config.min.js');
    }

    private function buildHeaderToolbar()
    {
        $this->page_header_toolbar_btn['catalog'] = array(
            'href' => $this->context->link->getAdminLink('LaboDataCatalogAdmin'),
            'desc' => $this->module->lc('Catalogue LaboData'),
            'icon' => 'process-icon-new',
        );
        $this->page_header_toolbar_btn['category'] = array(
            'href' => $this->context->link->getAdminLink('LaboDataCategoryAdmin'),
            'desc' => $this->module->lc('Marques/Caracteristiques'),
            'icon' => 'process-icon-new',
        );
    }
    public function renderList()
    {
        $account = new Account();

        $smarty = $this->context->smarty;
        $smarty->assign(array(
            'module_name'     => $this->module->name,
            'path_uri_img'    => $this->module->getPathUri() . '/views/img/',
            'message_error'   => $this->messageError,
            'message_content' => $this->messageContent,
            'account'         => $account->canConnect() ? $account->getData() : null,
            LaboDataQuery::CONF_EMAIL      => Configuration::get(LaboDataQuery::CONF_EMAIL),
            LaboDataQuery::CONF_SECRET_KEY => Configuration::get(LaboDataQuery::CONF_SECRET_KEY),
        ));

        return $smarty->fetch($this->getTemplatePath() . 'config.tpl');
    }

    public function postProcess()
    {
        if (Tools::isSubmit('submit_' . $this->module->name)) {
            return $this->saveContent();
        }

        return parent::postProcess();
    }

    private function saveContent()
    {
        if (Configuration::updateValue(LaboDataQuery::CONF_EMAIL, Tools::getValue(LaboDataQuery::CONF_EMAIL)) &&
            Configuration::updateValue(LaboDataQuery::CONF_SECRET_KEY, Tools::getValue(LaboDataQuery::CONF_SECRET_KEY))
        ) {
            $this->messageContent = $this->module->lc('Vos parametres ont ete enregistres');
            return true;
        }

        $this->messageError = true;
        $this->messageContent = $this->module->lc('Une erreur s\'est produite lors de l\'enregistrement de vos parametres');
        return false;
    }
}
