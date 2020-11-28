<?php
/**
 * LaboData for Prestashop
 *
 * @author 161 SARL <contact@161.io>
 * @copyright (c) 161 SARL, https://161.io
 * @license https://161.io
 */

use LaboDataPrestaShop\Api\Account;
use LaboDataPrestaShop\Api\Category as LaboDataCategory;
use LaboDataPrestaShop\Api\Product as LaboDataProduct;
use LaboDataPrestaShop\Api\Search;
use LaboDataPrestaShop\Controller\StaticAdminController;
use LaboDataPrestaShop\Import\ImportProduct;
use ModuleAdminController as NoTabModuleAdminController;

/**
 * @property LaboData $module
 */
class LaboDataCatalogAdminController extends NoTabModuleAdminController
{
    const ACTION_ADD  = 'add';
    const ACTION_EDIT = 'edit';
    const ACTION_BUY  = 'buy';

    public function __construct()
    {
        $this->bootstrap = true;

        parent::__construct();

        StaticAdminController::buildHeaderToolbar($this, $this->context);
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);

        //$this->addJS($this->module->getPathUri() . '/views/js/js-cookie.js');
        //$this->addJS($this->module->getPathUri() . '/views/js/catalog.js');
        $this->addJS($this->module->getPathUri() . '/views/js/js-cookie.min.js');
        $this->addJS($this->module->getPathUri() . '/views/js/catalog.min.js');
    }

    public function initContent()
    {
        $laboDataSearch = Search::getInstance();

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

        $laboDataSearch = Search::getInstance();

        $adminLink = $this->context->link->getAdminLink($this->controller_name);
        $smarty = $this->context->smarty;
        $smarty->assign(array(
            'labodata_redirect_autoconnect' => $adminLink . '&redirect=autoconnect',
            'labodata_redirect_autopay'     => $adminLink . '&redirect=autopay',
            'labodata_url_import'           => $adminLink,
            'labodata_credit'               => $laboDataSearch->getCredit(),
            'labodata_cost'                 => $laboDataSearch->getCostQuery(),
            'labodata_currency'             => '&euro;',
            'path_uri_img'    => $this->module->getPathUri() . '/views/img/',
            'form_controller' => $this->controller_name,
            'form_token'      => $this->token,
            'form_brand'      => $laboDataSearch->getBrandValue(),
            'form_q'          => $laboDataSearch->getQValue(),
            'form_order'      => $laboDataSearch->getOrderValue(),
            'form_purchase'   => $laboDataSearch->getPurchaseValue(),
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
        $searchInst = Search::getInstance();
        $cost = $searchInst->getCostQuery();

        foreach ($products as &$product) {
            $product['_purchaseFull'] = (!empty($product['purchase']['image'])
                                      && !empty($product['purchase']['content']));
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

            // Traductions
            $product['brand']['title'] = $searchInst->getTransItem($product['brand']);
            $product['title'] = $searchInst->getTransItem($product);
            $product['content'] = $searchInst->getTransItem($product, 'content');
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
            case 'autoconnect':
                Tools::redirect(Account::getInstance()->getAutoconnect());
                return true;
                // no break
            case 'autopay':
                Tools::redirect(Account::getInstance()->getAutopay());
                return true;
                // no break
        }
        return false;
    }

    private function xhrProcess()
    {
        $action = Tools::substr(Tools::getValue('action'), 0, 20);
        $id = (int) Tools::getValue('id'); // idLabodata (product)
        $type = Tools::substr(Tools::getValue('type'), 0, 20);
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

        $laboDataPrestashop = ImportProduct::getInstance();
        $laboDataProduct = new LaboDataProduct();
        if ($action) {
            $laboDataProduct->getProduct($id, $type);
            $json['apiResponse'] = $laboDataProduct->getLastResult();
        }


        switch ($action) {
            case self::ACTION_ADD: // Ajout force
                $psProduct = $laboDataPrestashop->addProduct($laboDataProduct);
                break;
            case self::ACTION_EDIT: // Modif. ou ajout si absent
                $psProduct = $laboDataPrestashop->editProduct($laboDataProduct);
                break;
            case self::ACTION_BUY: // Achat simple
                $json['success'] = (bool) $laboDataProduct->getId();
                break;
        }

        if ($psProduct && $psProduct->id) {
            $json['success'] = true;
            $json['product'] = $psProduct ? $psProduct->getFields() : null;
        }

        if (ob_get_length()) {
            ob_end_clean();
        }
        header('Content-Type: application/json');
        if (method_exists($this, 'ajaxRender')) {
            $this->ajaxRender(json_encode($json));
        } else {
            $this->ajaxDie(json_encode($json));
        }
    }
}
