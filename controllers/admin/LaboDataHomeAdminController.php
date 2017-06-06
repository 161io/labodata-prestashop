<?php
/**
 * LaboData for Prestashop
 *
 * @author 161 SARL <contact@161.io>
 * @copyright (c) 161 SARL, https://161.io
 * @license https://161.io
 */

use ModuleAdminController as NoTabModuleAdminController;

class LaboDataHomeAdminController extends NoTabModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;

        parent::__construct();
    }

    public function initContent()
    {
        Tools::redirectAdmin($this->context->link->getAdminLink('LaboDataCatalogAdmin'));
    }
}
