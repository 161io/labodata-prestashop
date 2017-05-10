<?php
/**
 * Copyright (c) 161 SARL, https://161.io
 */

use ModuleAdminController as NoTabModuleAdminController;

class LaboDataCatalogAdminController extends NoTabModuleAdminController
{
    const ACTION_ADD  = 'add';
    const ACTION_EDIT = 'edit';
    const ACTION_BUY  = 'buy';

    public function __construct()
    {
        $this->bootstrap = true;

        parent::__construct();

        $this->buildHeaderToolbar();
    }

    public function setMedia()
    {
        parent::setMedia();

        //$this->addJS($this->module->getPathUri() . '/js/js-cookie.js');
        $this->addJS($this->module->getPathUri() . '/js/js-cookie.min.js');
        //$this->addJS($this->module->getPathUri() . '/js/catalog.js');
        $this->addJS($this->module->getPathUri() . '/js/catalog.min.js');
    }

    private function buildHeaderToolbar()
    {
        $this->page_header_toolbar_btn['category'] = array(
            'href' => $this->context->link->getAdminLink('LaboDataCategoryAdmin'),
            'desc' => $this->module->l('Marques/Caractéristiques'),
            'icon' => 'process-icon-new',
        );
        $this->page_header_toolbar_btn['config'] = array(
            'href' => $this->context->link->getAdminLink('LaboDataConfigAdmin'),
            'desc' => $this->module->l('Configuration'),
            'icon' => 'process-icon-configure',
        );
    }

    public function initContent()
    {
        $laboDataSearch = LaboDataSearch::getInstance();

        // Connexion non configuree
        if (!$laboDataSearch->canConnect() || $laboDataSearch->isError()) {
            Tools::redirectAdmin($this->context->link->getAdminLink('LaboDataConfigAdmin'));
            return;
        }

        if ($this->isXmlHttpRequest()) {
            return $this->xhrProcess();
        }

        parent::initContent();
    }

    public function renderList()
    {
        if ($this->redirectTo()) {
            return '';
        }

        $laboDataSearch = LaboDataSearch::getInstance();

        $adminLink = $this->context->link->getAdminLink($this->controller_name);
        $smarty = $this->context->smarty;
        $smarty->assign(array(
            'labodata_redirect_autoconnect' => $adminLink . '&redirect=autoconnect',
            'labodata_redirect_autopay'     => $adminLink . '&redirect=autopay',
            'labodata_url_import'           => $adminLink,
            'labodata_credit'               => $laboDataSearch->getCredit(),
            'labodata_cost'                 => $laboDataSearch->getCostQuery(),
            'labodata_currency'             => '&euro;',
            'form_controller' => $this->controller_name,
            'form_token'      => $this->token,
            'form_brand'      => (int) Tools::getValue('brand', 0),
            'form_q'          => mb_substr(Tools::getValue('q', ''), 0, 200),
            'brands'          => LaboDataCategory::getInstance()->getBrands(),
            'products'   => $this->smartyProductsFilter($laboDataSearch->getProducts()),
            'pagination' => $laboDataSearch->getPagination(),
        ));

        return $smarty->fetch($this->getTemplatePath() . 'catalog.tpl');
    }

    /**
     * Injecter les donnees supplementaires pour Smarty
     *
     * @param array $products
     * @return array
     */
    private function smartyProductsFilter($products)
    {
        $cost = LaboDataSearch::getInstance()->getCostQuery();

        foreach ($products as &$product) {
            $product['_purchaseFull'] = (!empty($product['purchase']['image']) && !empty($product['purchase']['content']));
            $product['_purchaseFullCredit'] = '';

            if (!$product['_purchaseFull']) {
                if (empty($product['purchase']['image']) && empty($product['purchase']['content'])) {
                    $product['_purchaseFullCredit'] = $cost['full'];
                } elseif (empty($product['purchase']['image'])) {
                    $product['_purchaseFullCredit'] = $cost['image'];
                } else {
                    $product['_purchaseFullCredit'] = $cost['content'];
                }
            }
        }

        return $products;
    }

    /**
     * Redirections
     *
     * @return bool
     */
    private function redirectTo()
    {
        $redirect = Tools::getValue('redirect');
        switch ($redirect) {
            case 'autoconnect' :
                $autoconnect = new LaboDataAccountInformation();
                Tools::redirect($autoconnect->getAutoconnect());
                return true;
                // no break
            case 'autopay' :
                $autopay = new LaboDataAccountInformation();
                Tools::redirect($autopay->getAutopay());
                return true;
                // no break
        }
        return false;
    }

    private function xhrProcess()
    {
        $action = substr(Tools::getValue('action'), 0, 20);
        $id = (int) Tools::getValue('id'); // idLabodata (product)
        $type = substr(Tools::getValue('type'), 0, 20);
        $psProduct = null;

        $json = array(
            'action'  => $action,
            'id'      => $id,
            'type'    => $type,
            'success' => false,
        );

        if (!in_array($action, array(self::ACTION_ADD, self::ACTION_EDIT, self::ACTION_BUY))) {
            $action = null;
        }

        $laboDataPrestashop = LaboDataPrestashop::getInstance();
        $laboDataProduct = new LaboDataProduct();
        if ($action) {
            $laboDataProduct->getProduct($id, $type);
            $json['apiResponse'] = $laboDataProduct->getLastResult();
        }


        switch ($action) {
            case self::ACTION_ADD : // Ajout force
                $psProduct = $laboDataPrestashop->addProduct($laboDataProduct);
                break;
            case self::ACTION_EDIT : // Modif. ou ajout si absent
                $psProduct = $laboDataPrestashop->editProduct($laboDataProduct);
                break;
            case self::ACTION_BUY : // Achat simple
                $json['success'] = (bool) $laboDataProduct->getId();
                break;
        }

        if ($psProduct && $psProduct->id) {
            $json['success'] = true;
            $json['product'] = $psProduct ? $psProduct->getFields() : null;
        }

        ob_end_clean();
        header('Content-Type: application/json');
        $this->ajaxDie(json_encode($json));
    }
}
