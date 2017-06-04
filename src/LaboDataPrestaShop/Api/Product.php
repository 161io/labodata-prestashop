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
 * @method static Product getInstance()
 */
class Product extends Query
{
    const ROOT_KEY = 'product';

    const TYPE_IMAGE   = 'image';
    const TYPE_CONTENT = 'content';
    const TYPE_FULL    = 'full';

    /**
     * Langues supportees par LaboData
     *
     * @var string[]
     */
    protected $langs = array('fr');

    /**
     * @var string
     */
    protected $lastType;

    /**
     * @var array
     */
    protected $lastResult;

    /**
     * @param int $id
     * @param string $type
     */
    public function __construct($id = null, $type = null)
    {
        if ($id && $type) {
            $this->getProduct($id, $type);
        }
    }

    /**
     * @return string[]
     */
    public function getApiTypes()
    {
        return array(self::TYPE_IMAGE, self::TYPE_CONTENT, self::TYPE_FULL);
    }

    /**
     * @param string $lang
     * @return string
     */
    public function keyLang($lang)
    {
        if (in_array($lang, $this->langs)) {
            return $lang;
        }
        return $this->langs[0];
    }

    /**
     * Produit dans son ensemble
     *
     * @param int $id
     * @param string $type
     * @return array|null
     */
    public function getProduct($id = null, $type = null)
    {
        if (null === $id && null === $type && $this->getId()) {
            return $this->lastResult[self::ROOT_KEY];
        }

        if (!$id || !$type || !in_array($type, $this->getApiTypes())) {
            $this->lastType = null;
            $this->lastResult = null;
            return null;
        }

        $id = (int) $id;
        if ($this->lastType == $type && $this->getId() == $id) {
            // Demande identique
            return $this->lastResult[self::ROOT_KEY];
        }

        $this->lastType = $type;
        $this->lastResult = $this->query(self::URL . self::API . '/product/' . $type . '.json', array(
            'id' => $id,
        ));

        if (isset($this->lastResult[self::ROOT_KEY])) {
            return $this->lastResult[self::ROOT_KEY];
        }
        return null;
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
     * @return string
     */
    public function getLastType()
    {
        return $this->lastType;
    }

    /**
     * @return array
     */
    public function getLastResult()
    {
        return $this->lastResult;
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



    /**
     * @param string $key
     * @return mixed
     */
    public function getProductKey($key)
    {
        if (isset($this->lastResult[self::ROOT_KEY][$key])) {
            return $this->lastResult[self::ROOT_KEY][$key];
        }
        return null;
    }

    /**
     * Identifiant du produit dans LaboData
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->getProductKey('id');
    }

    /**
     * @return string|null
     */
    public function getBrandId()
    {
        $brand = $this->getProductKey('brand');
        if ($brand) {
            return $brand['id'];
        }
        return null;
    }

    /**
     * @return string|null
     */
    public function getBrandName()
    {
        $brand = $this->getProductKey('brand');
        if ($brand) {
            return $brand['name'];
        }
        return null;
    }

    /**
     * @param string $lang
     * @return string|null
     */
    public function getBrandTitle($lang = 'fr')
    {
        $brand = $this->getProductKey('brand');
        if ($brand) {
            return $brand['title_'.$this->keyLang($lang)];
        }
        return null;
    }

    /**
     * @return string|null
     */
    public function getEan13()
    {
        return $this->getProductKey('code');
    }

    /**
     * @return int|null
     */
    public function getWeight()
    {
        return $this->getProductKey('weight');
    }

    /**
     * @return bool|null
     */
    public function getBio()
    {
        return $this->getProductKey('bio');
    }

    /**
     * @param string $lang
     * @return string|null
     */
    public function getTitle($lang = 'fr')
    {
        return $this->getProductKey('title_'.$this->keyLang($lang));
    }

    /**
     * @param string $lang
     * @return string|null
     */
    public function getContent($lang = 'fr')
    {
        return $this->getProductKey('content_'.$this->keyLang($lang));
    }

    /**
     * @param string $lang
     * @return string[]|null
     */
    public function getAdditionalContent($lang = 'fr')
    {
        $additionalContent = $this->getProductKey('additional_content');
        if (!$additionalContent) {
            return null;
        }

        $lang = $this->keyLang($lang);
        foreach ($additionalContent as &$item) {
            $item['title'] = $item['title_' . $lang];
            $item['content'] = $item['content_' . $lang];
        }

        return $additionalContent;
    }

    /**
     * @return int[]
     */
    public function getCategoryIds()
    {
        $ids = array();
        $categories = $this->getProductKey('categories');
        if (!$categories) {
            return $ids;
        }

        foreach ($categories as $categoryType => $_categories) {
            foreach ($_categories as $category) {
                $ids[] = (int) $category['id'];
            }
        }
        return $ids;
    }

    /**
     * @param int|null $idx
     * @return string|null
     */
    public function getImage($idx = 0)
    {
        $images = $this->getProductKey('images');
        if (null === $idx) {
            return $images;
        }
        if (isset($images[$idx])) {
            return $images[$idx];
        }
        return null;
    }

    /**
     * @return float|null
     */
    public function getVat()
    {
        $retailPrice = $this->getProductKey('retail_price');
        if (isset($retailPrice['vat'])) {
            return $retailPrice['vat'];
        }
        return null;
    }
}
