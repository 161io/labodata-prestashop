<?php
/**
 * Copyright (c) 161 SARL, https://161.io
 */

/**
 * Rechercher dans le catalogue LaboData
 */
class LaboDataSearch extends LaboDataQuery
{
    /**
     * @var self
     */
    protected static $instance;

    /**
     * @return self
     */
    public static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * @var array
     */
    protected $lastResult;

    /**
     * Lancer la recherche
     *
     * @param array|string $options
     * @return array
     */
    public function search($options = 'auto')
    {
        if ('auto' == $options) {
            $options = array(
                'brand' => Tools::getValue('brand', ''),
                'q'     => Tools::getValue('q', ''),
                'page'  => Tools::getValue('page', ''),
            );
        }

        $this->lastResult = $this->query(self::URL . self::API . '/product/search.json', $options);
        return $this->lastResult;
    }

    /**
     * Erreur de l'api
     *
     * @return bool
     */
    public function isError()
    {
        $result = $this->getLastResult();
        return !empty($result['error']);
    }

    /**
     * Lancer la recherche automatiquement
     *
     * @return array
     */
    public function getLastResult()
    {
        if (null === $this->lastResult) {
            $this->search();
        }
        return $this->lastResult;
    }

    /**
     * Les produits de la recherche
     *
     * @return array
     */
    public function getProducts()
    {
        $result = $this->getLastResult();
        if (isset($result['items'])) {
            return $result['items'];
        }
        return array();
    }

    /**
     * Pagination du dernier resultat de la recherche
     *
     * @return array
     */
    public function getPagination()
    {
        $result = $this->getLastResult();
        $link = Context::getContext()->link->getAdminLink('LaboDataCatalogAdmin');

        $pageMax = 4;
        $first = 1;
        $last = $result['page_length'];
        $prev = $result['page_number'] - 1;
        $next = $result['page_number'] + 1;
        if ($prev < $first) { $prev = $first; }
        if ($next > $last) {$next = $last; }
        $paginationStart = $result['page_number'] - $pageMax;
        $paginationEnd = $result['page_number'] + $pageMax;
        if ($paginationStart < $first) { $paginationStart = $first; }
        if ($paginationEnd > $last ) { $paginationEnd = $last; }


        $pagination = array();
        $pagination[] = array(
            'label' => '&laquo;',
            'href'  => $link . '&p=' . $first,
        );
        $pagination[] = array(
            'label' => '&lsaquo;',
            'href'  => $link . '&p=' . $prev,
        );
        for ($p = $paginationStart; $p <= $paginationEnd; ++$p) {
            $pagination[] = array(
                'label'  => $p,
                'href'   => $link . '&p=' . $p,
                'active' => ($p == $result['page_number']),
            );
        }
        $pagination[] = array(
            'label' => '&rsaquo;',
            'href'  => $link . '&p=' . $next,
        );
        $pagination[] = array(
            'label' => '&raquo;',
            'href'  => $link . '&p=' . $last,
        );

        return $pagination;
    }

    /**
     * Cout d'une photo/descriptif
     *
     * @return array
     */
    public function getCostQuery()
    {
        $result = $this->getLastResult();
        if (isset($result['cost_query'])) {
            $array = array_map(function($str) {
                return str_replace('.', ',', $str);
            }, $result['cost_query']);
            return $array;
        }
        return array();
    }

    /**
     * Credit disponible
     *
     * @return string
     */
    public function getCredit()
    {
        $result = $this->getLastResult();
        if (isset($result['credit'])) {
            return str_replace('.', ',', $result['credit']);
        }
        return 'Error';
    }
}
