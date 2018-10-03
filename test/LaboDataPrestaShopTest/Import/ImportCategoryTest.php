<?php
/**
 * LaboData for Prestashop
 *
 * @author 161 SARL <contact@161.io>
 * @copyright (c) 161 SARL, https://161.io
 * @license https://161.io
 */

namespace LaboDataPrestaShopTest\Import;

use LaboDataPrestaShop\Import\ImportCategory;
use PHPUnit\Framework\TestCase;

class ImportCategoryTest extends TestCase
{
    const ID_LABODATA = 99992;

    public function testAddCategoryLabodata()
    {
        $category = (object) array(
            'id' => self::ID_LABODATA - 1,
        );
        $laboDataCategory = array(
            'id' => self::ID_LABODATA,
        );
        $this->assertTrue(ImportCategory::getInstance()->addCategoryLabodata($category, $laboDataCategory));
        $this->assertArrayHasKey(self::ID_LABODATA, ImportCategory::getInstance()->getCategoryLabodataIds(false));
    }

    public function testDeleteCategoryLabodata()
    {
        $laboDataCategory2 = array(
            'id' => self::ID_LABODATA,
        );
        $this->assertTrue(ImportCategory::getInstance()->deleteCategoryLabodata($laboDataCategory2));
        $this->assertArrayHasKey(self::ID_LABODATA, ImportCategory::getInstance()->getCategoryLabodataIds(false));

        ImportCategory::getInstance(true);
        $this->assertArrayNotHasKey(self::ID_LABODATA, ImportCategory::getInstance()->getCategoryLabodataIds(false));
    }
}
