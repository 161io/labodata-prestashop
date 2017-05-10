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
    $module->_uninstallTabs();
    $module->_installTables();
    $module->_installTabs();
    return true;
}
