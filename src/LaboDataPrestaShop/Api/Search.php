<?php
/**
 * LaboData for Prestashop
 *
 * @author 161 SARL <contact@161.io>
 * @copyright (c) 161 SARL, https://161.io
 * @license https://161.io
 */

namespace LaboDataPrestaShop\Api;

use Context;
use Tools;

/**
 * Rechercher dans le catalogue LaboData
 *
 * @method static Search getInstance()
 */
class Search extends Query
{
    /**
     * @const string Valeur par default
     */
    const ORDER_BY_TITLE_ASC = 'title-asc';

    /**
     * @const string
     */
    const ORDER_BY_DATE_DESC = 'date-desc';

    /**
     * @var array
     */
    protected $lastResult;

    /**
     * $_GET: Marque
     *
     * @return int
     */
    public function getBrandValue()
    {
        return (int) Tools::getValue('brand', 0);
    }

    /**
     * $_GET: Recherche
     *
     * @return string
     */
    public function getQValue()
    {
        return Tools::substr(trim(Tools::getValue('q', '')), 0, 200);
    }

    /**
     * $_GET: Tri
     *
     * @return string
     */
    public function getOrderValue()
    {
        $values = array(
            self::ORDER_BY_TITLE_ASC,
            self::ORDER_BY_DATE_DESC,
        );

        $value = trim(Tools::getValue('order', $values[0]));
        if (!in_array($value, $values)) {
            $value = $values[0];
        }
        return $value;
    }

    /**
     * $_GET: Page
     *
     * @return int
     */
    public function getPageNumberValue()
    {
        return (int) Tools::getValue('p', 1);
    }



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
                'brand' => $this->getBrandValue(),
                'q'     => $this->getQValue(),
                'order' => $this->getOrderValue(),
                'page'  => $this->getPageNumberValue(),
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
        $queryBrand = $this->getBrandValue();
        $queryQ = $this->getQValue();
        $queryOrder = $this->getOrderValue();
        if ($queryBrand) {
            $link .= '&brand=' . $queryBrand;
        }
        if ($queryQ) {
            $link .= '&q=' . urlencode($queryQ);
        }
        if ($queryOrder) {
            $link .= '&order=' . urlencode($queryOrder);
        }

        $pageMax = 4;
        $first = 1;
        $last = $result['page_length'];
        $prev = $result['page_number'] - 1;
        $next = $result['page_number'] + 1;
        if ($prev < $first) {
            $prev = $first;
        }
        if ($next > $last) {
            $next = $last;
        }
        $paginationStart = $result['page_number'] - $pageMax;
        $paginationEnd = $result['page_number'] + $pageMax;
        if ($paginationStart < $first) {
            $paginationStart = $first;
        }
        if ($paginationEnd > $last) {
            $paginationEnd = $last;
        }


        $pagination = array();
        $pagination[] = array(
            'label'  => '&laquo;',
            'href'   => $link . '&p=' . $first,
            'active' => false,
        );
        $pagination[] = array(
            'label'  => '&lsaquo;',
            'href'   => $link . '&p=' . $prev,
            'active' => false,
        );
        for ($p = $paginationStart; $p <= $paginationEnd; ++$p) {
            $pagination[] = array(
                'label'  => $p,
                'href'   => $link . '&p=' . $p,
                'active' => ($p == $result['page_number']),
            );
        }
        $pagination[] = array(
            'label'  => '&rsaquo;',
            'href'   => $link . '&p=' . $next,
            'active' => false,
        );
        $pagination[] = array(
            'label'  => '&raquo;',
            'href'   => $link . '&p=' . $last,
            'active' => false,
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
            $array = array_map(function ($str) {
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
