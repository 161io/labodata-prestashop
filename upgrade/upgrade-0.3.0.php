<?php
/**
 * Copyright (c) 161 SARL, https://161.io
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * @param LaboData $module
 * @return bool
 */
function upgrade_module_0_3_0($module) {
    $install = new LaboDataPrestaShop\Install\Install($module);
    $uninstall = new LaboDataPrestaShop\Install\Uninstall($module);

    $uninstall->tab();
    $install->table();
    $install->tab();

    return true;
}
