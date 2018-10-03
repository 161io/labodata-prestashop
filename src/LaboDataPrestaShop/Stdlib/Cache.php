<?php
/**
 * LaboData for Prestashop
 *
 * @author 161 SARL <contact@161.io>
 * @copyright (c) 161 SARL, https://161.io
 * @license https://161.io
 */

namespace LaboDataPrestaShop\Stdlib;

/**
 * Mini-Cache
 */
class Cache
{
    /**
     * @param string $key
     * @return array|null
     */
    public static function get($key)
    {
        $filename = _PS_CACHE_DIR_ . 'module_labodata_'.$key.'.php';
        if (file_exists($filename)) {
            $dump = include $filename;
            if (isset($dump['time'], $dump['data']) && $dump['time'] == date('ymdH')) {
                return $dump['data'];
            }
        }
        return null;
    }

    /**
     * @param string $key
     * @param array $data
     */
    public static function set($key, $data)
    {
        $filename = _PS_CACHE_DIR_ . 'module_labodata_'.$key.'.php';
        $dump = array(
            'time' => date('ymdH'),
            'data' => $data,
        );
        file_put_contents($filename, '<?php return ' . var_export($dump, true) . ';');
    }

    /**
     * Supprimer le mini-cache lors de la desinstallation
     */
    public static function clear()
    {
        $files = glob(_PS_CACHE_DIR_ . 'module_labodata_*.php');
        foreach ($files as $filename) {
            if (is_file($filename)) {
                @unlink($filename);
            }
        }
    }
}
