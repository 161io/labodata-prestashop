<?php
/**
 * Copyright (c) 161 SARL, https://161.io
 */

class LaboDataQuery
{
    const URL = 'https://www.labodata.fr';
    const API = '/api/v1';

    const CONF_EMAIL      = 'MOD_LABODATA_EMAIL';
    const CONF_SECRET_KEY = 'MOD_LABODATA_KEY';

    /**
     * @var mixed
     */
    protected $error;

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
            echo '<p>cURL was not found <a href="http://php.net/manual/en/book.curl.php" target="_blank">http://php.net/manual/en/book.curl.php</a></p>';
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
}
