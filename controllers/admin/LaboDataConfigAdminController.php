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

        //$this->addJS($this->module->getPathUri() . '/js/config.js');
        $this->addJS($this->module->getPathUri() . '/js/config.min.js');
    }

    private function buildHeaderToolbar()
    {
        $this->page_header_toolbar_btn['catalog'] = array(
            'href' => $this->context->link->getAdminLink('LaboDataCatalogAdmin'),
            'desc' => $this->module->l('Catalogue LaboData'),
            'icon' => 'process-icon-new',
        );
        $this->page_header_toolbar_btn['category'] = array(
            'href' => $this->context->link->getAdminLink('LaboDataCategoryAdmin'),
            'desc' => $this->module->l('Marques/Caractéristiques'),
            'icon' => 'process-icon-new',
        );
    }
    public function renderList()
    {
        $account = new Account();

        $smarty = $this->context->smarty;
        $smarty->assign(array(
            'module_name'     => $this->module->name,
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
            $this->messageContent = $this->module->l('Vos paramètres ont été enregistrés.');
            return true;
        }

        $this->messageError = true;
        $this->messageContent = $this->module->l('Une erreur s\'est produite lors de l\'enregistrement de vos paramètres.');
        return false;
    }
}
