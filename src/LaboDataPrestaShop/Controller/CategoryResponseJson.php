<?php
/**
 * LaboData for Prestashop
 *
 * @author 161 SARL <contact@161.io>
 * @copyright (c) 161 SARL, https://161.io
 * @license https://161.io
 */

namespace LaboDataPrestaShop\Controller;

use Tools;

class CategoryResponseJson
{
    /**
     * @var string
     */
    public $action;

    /**
     * @var int
     */
    public $id;

    /**
     * @var int
     */
    public $idPrestashop;

    /**
     * @var string
     */
    public $type;

    /**
     * @var array
     */
    public $ldCategory;

    /**
     * @var int
     */
    public $psIdObject;

    /**
     * @var string
     */
    public $psNameObject;

    /**
     * @var string
     */
    public $psIdParentObject;
    /**
     * @var string
     */
    public $psNameParentObject;

    /**
     * @var string
     */
    public $growlType;

    /**
     * @var string
     */
    public $growlMessage;



    /**
     * Initialisation
     */
    public function init()
    {
        $this->action = Tools::substr(Tools::getValue('action'), 0, 20);
        $this->id = (int) Tools::getValue('id'); // idLabodata (category)

        $idPrestashop = (int) Tools::getValue('idPrestashop');
        if ($idPrestashop) {
            $this->idPrestashop = $idPrestashop; // idPrestashop (category)
        }
    }

    /**
     * @return string
     */
    public function toString()
    {
        return json_encode($this);
    }

    /**
     * @return bool
     */
    public function deleteIdPrestashop()
    {
        return -1 == $this->idPrestashop;
    }
}
