<?php
/**
 * LaboData for Prestashop
 *
 * @author 161 SARL <contact@161.io>
 * @copyright (c) 161 SARL, https://161.io
 * @license https://161.io
 */

require_once dirname(__FILE__) . '/LaboDataCategoryAdminController.php';

/**
 * @property LaboData $module
 */
class LaboDataTreeAdminController extends LaboDataCategoryAdminController
{
    /**
     * @inheritDoc
     */
    protected $treeMode = true;
}
