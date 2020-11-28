<?php
/**
 * LaboData for Prestashop
 *
 * @author 161 SARL <contact@161.io>
 * @copyright (c) 161 SARL, https://161.io
 * @license https://161.io
 */

namespace LaboDataPrestaShop\Api;

use Configuration;
use LaboDataPrestaShop\Stdlib\Cache;

class Query
{
    const URL = 'https://www.labodata.com';
    const API = '/api/v1';

    const CONF_EMAIL      = 'MOD_LABODATA_EMAIL';
    const CONF_SECRET_KEY = 'MOD_LABODATA_KEY';

    /**
     * @var mixed
     */
    protected $error;

    /**
     * @var array
     */
    protected $langs;

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
     * @return mixed
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @param string $urlJson
     * @param array $params
     * @return array
     */
    public function query($urlJson, $params = array())
    {
        if (!in_array('curl', get_loaded_extensions())) {
            echo '<p>cURL was not found <a href="http://php.net/manual/en/book.curl.php">'
                . 'http://php.net/manual/en/book.curl.php</a></p>';
            exit;
        }

        $postfields = array_merge($params, array(
            'email'  => Configuration::get(self::CONF_EMAIL),
            'secret' => Configuration::get(self::CONF_SECRET_KEY),
        ));

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $urlJson);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Facultatif selon votre serveur
        curl_setopt($ch, CURLOPT_USERAGENT, 'LaboData Prestashop');
        $output = curl_exec($ch);
        curl_close($ch);

        $array = json_decode($output, true);
        if (null === $array) {
            $this->error = $output;
            return array();
        }
        return $array;
    }

    /**
     * @return bool
     */
    public function canConnect()
    {
        return Configuration::get(self::CONF_EMAIL) && Configuration::get(self::CONF_SECRET_KEY);
    }

    /**
     * @return array
     */
    public function getLangs()
    {
        return $this->langs;
    }

    /**
     * @param array $langs
     * @return self
     */
    protected function setLangs($langs)
    {
        $this->langs = (array) $langs;
        return $this;
    }

    /**
     * @param array $result sauf account.json
     * @param bool $setCache
     * @return self
     */
    protected function setLangsFromResult($result, $setCache = true)
    {
        if (!empty($result['langs'])) {
            $this->setLangs($result['langs']);
            if ($setCache) {
                Cache::set('langs', $this->langs);
            }
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getDefaultLang()
    {
        $langs = $this->getLangs();
        return (string) (isset($langs['default']) ? $langs['default'] : 'fr');
    }

    /**
     * @return array
     */
    public function getActiveLangs()
    {
        $langs = $this->getLangs();
        return (array) (isset($langs['actives']) ? $langs['actives'] : ['fr']);
    }

    /**
     * Traduction d'une cle selon les langues disponibles
     *
     * @param array $item
     * @param string $key
     * @param string $lang
     * @param string $defaultValue
     * @return string
     */
    public function getTransItem($item, $key = 'title', $lang = null, $defaultValue = '')
    {
        if ($lang && !empty($item[$key . '_' . $lang])) {
            return $item[$key . '_' . $lang];
        }

        $lang = $this->getDefaultLang();
        if (!empty($item[$key . '_' . $lang])) {
            return $item[$key . '_' . $lang];
        }
        if (count($this->getActiveLangs()) < 2) {
            return $defaultValue;
        }

        foreach ($this->getActiveLangs() as $lang) {
            if (!empty($item[$key . '_' . $lang])) {
                return $item[$key . '_' . $lang];
            }
        }
        return $defaultValue;
    }

    /**
     * Creer le cle "title"
     *
     * @param array $categories
     * @return array
     */
    public function setCategoryTitles($categories)
    {
        foreach ($categories as &$category) {
            $category['title'] = $this->getTransItem($category);
            if (isset($category['items'])) { // type
                $category['items'] = $this->setCategoryTitles($category['items']);
            }
            if (isset($category['children'])) { // sous-category
                $category['children'] = $this->setCategoryTitles($category['children']);
            }
        }
        return $categories;
    }
}
