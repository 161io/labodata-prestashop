<?php
/**
 * LaboData for Prestashop
 *
 * @author 161 SARL <contact@161.io>
 * @copyright (c) 161 SARL, https://161.io
 * @license https://161.io
 */

namespace LaboDataPrestaShop\Api;

/**
 * Compte LaboData et acces direct ( liens de redirection )
 *
 * @method static Account getInstance()
 */
class Account extends Query
{
    /**
     * @var array
     */
    protected $data;

    /**
     * @param string $key
     * @return array|string
     */
    public function getData($key = null)
    {
        if (null === $this->data) {
            $this->data = $this->query(self::URL . self::API . '/account.json');
        }
        if ($key) {
            if (isset($this->data[$key])) {
                return $this->data[$key];
            }
            return null;
        }
        return $this->data;
    }

    /**
     * @return string
     */
    public function getCredit()
    {
        return $this->getData('credit');
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->getData('email');
    }

    /**
     * @return string
     */
    public function getSociety()
    {
        return $this->getData('society');
    }

    /**
     * @return string
     */
    public function getLastname()
    {
        return $this->getData('lastname');
    }

    /**
     * @return string
     */
    public function getFirstname()
    {
        return $this->getData('firstname');
    }



    /**
     * Lien de connexion
     *
     * @return string
     */
    public function getAutoconnect()
    {
        $result = $this->query(self::URL . self::API . '/autoconnect.json');
        if (isset($result['autoconnect'])) {
            return $result['autoconnect'];
        }
        return self::URL;
    }

    /**
     * Lien de paiement
     *
     * @return string
     */
    public function getAutopay()
    {
        $result = $this->query(self::URL . self::API . '/autopay.json');
        if (isset($result['autoconnect'])) {
            return $result['autoconnect'];
        }
        return self::URL;
    }
}
