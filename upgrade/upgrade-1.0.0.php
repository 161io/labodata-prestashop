<?php
/**
 * LaboData for Prestashop
 *
 * @author 161 SARL <contact@161.io>
 * @copyright (c) 161 SARL, https://161.io
 * @license https://161.io
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * @param LaboData $module
 * @return bool
 */
function upgrade_module_1_0_0($module)
{
    $install = new LaboDataPrestaShop\Install\Install($module);
    $uninstall = new LaboDataPrestaShop\Install\Uninstall($module);

    $uninstall->tab();
    $uninstall->cache();
    $install->tab();

    return true;
}
