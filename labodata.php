<?php
/**
 * Copyright (c) 161 SARL, https://161.io
 */

//error_reporting(E_ALL); ini_set('display_errors', '1');
if (!defined('_PS_VERSION_')) {
    exit;
}

require __DIR__ . '/vendor/autoload.php';

/**
 * Dev.mode: config/defines.inc.php _PS_MODE_DEV_
 *
 * @link http://developers.prestashop.com/module/05-CreatingAPrestaShop17Module/index.html
 * @link http://build.prestashop.com/prestashop-ui-kit/
 * @link https://www.prestasoo.com/fr/Blog/prestashop-1-6-icons-list.html
 */
class LaboData extends Module
{
    /**
     * @const string Type d'importation des categories LaboData ( category ou feature )
     */
    //const MODE_CATEGORY = 'category'; // 0.2.0
    const MODE_CATEGORY = 'feature'; // 0.3.0

    /**
     * @var AppKernel
     */
    protected $kernel;

    public function __construct()
    {
        $this->name = 'labodata';
        $this->tab = 'others';
        $this->version = '0.3.1';
        $this->author = '161 SARL';

        // 0 = Front // 1 = Back-office
        $this->need_instance = 1;

        $this->ps_versions_compliancy = array('min' => '1.6.1.0', 'max' => _PS_VERSION_);
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = 'LaboData';
        $this->description = $this->l('Constituez le catalogue de votre pharmacie ou parapharmacie en ligne avec LaboData avec +12000 fiches produits avec photos et descriptifs. Menu : "Catalogue" >> "LaboData"');
        $this->confirmUninstall = $this->l('Êtes-vous sûr de vouloir désinstaller le module ?');

        if (!Configuration::get('LABODATA_NAME')) {
            $this->warning = $this->l('Aucun module portant ce nom n\'a été trouvé');
        }
    }

    /**
     * @return AppKernel
     * @since Prestashop 1.7
     */
    public function getKernel()
    {
        if (null === $this->kernel) {
            global $kernel;
            $this->kernel = !empty($kernel) && $kernel instanceof AppKernel ? $kernel : false;
        }
        return $this->kernel;
    }

    public function install()
    {
        $install = new LaboDataPrestaShop\Install\Install($this);

        $this->_clearCache('*');
        if (!parent::install()
            || !$install->configuration()
            || !$install->tab()
            || !$install->table()
        ) {
            return false;
        }
        return true;
    }

    public function uninstall()
    {
        $uninstall = new LaboDataPrestaShop\Install\Uninstall($this);

        $this->_clearCache('*');
        if (!parent::uninstall()
            || !$uninstall->configuration()
            || !$uninstall->tab()
            //|| !$uninstall->table()
            || !$uninstall->cache()
        ) {
            return false;
        }
        return true;
    }

    /**
     * Configurer
     *
     * @return string
     */
    public function getContent()
    {
        Tools::redirectAdmin($this->context->link->getAdminLink('LaboDataConfigAdmin'));
        return '';
    }

    /**
     * Traduction pour les controllers ( Prestashop 1.6 et 1.7 )
     *
     * @param  string $string
     * @return string
     */
    public function lc($string)
    {
        switch ($string) {
            case 'Catalogue LaboData' : return $this->l('Catalogue LaboData');
            case 'Marques/Caracteristiques' : return $this->l('Marques/Caractéristiques');
            case 'Configuration' : return $this->l('Configuration');
            // LaboDataCatalogAdminController
            // LaboDataCategoryAdminController
            case 'id' : return $this->l('id');
            case 'Titre' : return $this->l('Titre');
            case 'Marque creee' : return $this->l('Marque créée :');
            case 'Erreur lors de la creation de la marque' : return $this->l('Erreur lors de la création de la marque');
            case 'Caracteristique (valeur)' : return $this->l('Caractéristique (valeur) :');
            case 'Erreur lors de la creation de la caracteristique (valeur)' : return $this->l('Erreur lors de la création de la caractéristique (valeur)');
            case 'Categorie creee' : return $this->l('Catégorie créée :');
            case 'Erreur lors de la creation de la categorie' : return $this->l('Erreur lors de la création de la catégorie');
            case 'Ajouter' : return $this->l('Ajouter');
            // LaboDataConfigAdminController
            case 'Vos parametres ont ete enregistres' : return $this->l('Vos paramètres ont été enregistrés.');
            case 'Une erreur s\'est produite lors de l\'enregistrement de vos parametres' : return $this->l('Une erreur s\'est produite lors de l\'enregistrement de vos paramètres.');
        }
        return $string;
    }
}
