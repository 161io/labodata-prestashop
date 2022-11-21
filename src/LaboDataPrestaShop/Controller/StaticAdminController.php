<?php
/**
 * LaboData for Prestashop
 *
 * @author 161 SARL <contact@161.io>
 * @copyright (c) 161 SARL, https://161.io
 * @license https://161.io
 */

namespace LaboDataPrestaShop\Controller;

abstract class StaticAdminController
{
    /**
     * @param \LaboDataCategoryAdminController|\ModuleAdminController $self
     * @param \Context $context
     */
    public static function buildHeaderToolbar($self, $context)
    {
        $class = get_class($self);

        if ('LaboDataCatalogAdminController' !== $class) {
            $self->page_header_toolbar_btn['ld_catalog'] = array(
                'href' => $context->link->getAdminLink('LaboDataCatalogAdmin'),
                'desc' => $self->module->lc('Catalogue LaboData'),
                'icon' => 'process-icon-new',
            );
        }
        if ('LaboDataCategoryAdminController' !== $class) {
            $self->page_header_toolbar_btn['ld_category'] = array(
                'href' => $context->link->getAdminLink('LaboDataCategoryAdmin'),
                'desc' => $self->module->lc('Marques/Caractéristiques'),
                'icon' => 'process-icon-new',
            );
        }
        if ('LaboDataTreeAdminController' !== $class) {
            $self->page_header_toolbar_btn['ld_tree'] = array(
                'href' => $context->link->getAdminLink('LaboDataTreeAdmin'),
                'desc' => $self->module->lc('Catégories Para./Médicament'),
                'icon' => 'process-icon-new',
            );
        }
        if ('LaboDataConfigAdminController' !== $class) {
            $self->page_header_toolbar_btn['ld_config'] = array(
                'href' => $context->link->getAdminLink('LaboDataConfigAdmin'),
                'desc' => $self->module->lc('Configuration'),
                'icon' => 'process-icon-configure',
            );
        }
    }

    /**
     * @return bool
     */
    public static function isPrestaShopV178()
    {
        return version_compare(_PS_VERSION_, '1.7.8', '>=');
    }
}
