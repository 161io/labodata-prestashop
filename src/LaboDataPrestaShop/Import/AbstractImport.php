<?php
/**
 * LaboData for Prestashop
 *
 * @author 161 SARL <contact@161.io>
 * @copyright (c) 161 SARL, https://161.io
 * @license https://161.io
 */

namespace LaboDataPrestaShop\Import;

use Language;
use Configuration;

abstract class AbstractImport
{
    /**
     * @var Language
     */
    protected $lang;

    /**
     * @param bool $renew
     * @return self
     */
    public static function getInstance($renew = false)
    {
        static $instance;
        if (null === $instance || $renew) {
            $instance = new static();
        }
        return $instance;
    }

    /**
     * Langue par defaut
     *
     * @param bool $toInt
     * @return int|Language
     */
    public function getLang($toInt = true)
    {
        if (null === $this->lang) {
            $this->lang = new Language(Configuration::get('PS_LANG_DEFAULT'));
        }
        if ($toInt) {
            return (int) $this->lang->id;
        }
        return $this->lang;
    }

    /**
     * @return string
     */
    public function getLangCode()
    {
        return $this->getLang(false)->iso_code;
    }
}
