<?php
/**
 * LaboData for Prestashop
 *
 * @author 161 SARL <contact@161.io>
 * @copyright (c) 161 SARL, https://161.io
 * @license https://161.io
 */

namespace LaboDataPrestaShopTest;

use Db;
use PHPUnit\Framework\TestCase;

class DbTest extends TestCase
{
    public function testGetInstance()
    {
        $this->assertContains(date('Y-m-d'), Db::getInstance()->getValue('SELECT NOW()'));
    }
}
