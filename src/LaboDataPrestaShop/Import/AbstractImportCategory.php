<?php
/**
 * LaboData for Prestashop
 *
 * @author 161 SARL <contact@161.io>
 * @copyright (c) 161 SARL, https://161.io
 * @license https://161.io
 */

namespace LaboDataPrestaShop\Import;

abstract class AbstractImportCategory extends AbstractImport
{
    /**
     * @var string
     */
    protected $labodataColumn = 'id_labodata';

    /**
     * @var int[]
     */
    protected $dataLabodataIds;

    /**
     * Table `_labodata` utilisee
     *
     * @return string
     */
    abstract public function getTable();
}
