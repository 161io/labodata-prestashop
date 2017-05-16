<?php
/**
 * Copyright (c) 161 SARL, https://161.io
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
     * @var string
     */
    protected $labodataColumn = 'id_labodata';

    /**
     * @return self
     */
    public static function getInstance()
    {
        static $instance;
        if (null === $instance) {
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
