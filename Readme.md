# LaboData pour Prestashop

Module https://www.labodata.com permettant d'importer des fiches produits dans Prestashop version 1.6 et 1.7


## FAQ

### Comment obtenir ma clé API ?

* Vous devez vous inscrire sur le site : https://www.labodata.com
* Puis dans la zone **Mon compte**, consulter la section **Mon compte et API**


### Comment installer le module LaboData depuis GitHub ?

* Télécharger le master : https://github.com/161io/labodata-prestashop
* Extraire le fichier zip dans le dossier `modules`
* Veiller à ce que le dossier du module soit bien nommé `labodata`
* Enfin, vous devez configurer vos accès dans l'onglet **Configuration** du module


### Comment personnaliser les templates d'injection du "Résumé" et de la "Description"

Modifier l'un des fichiers `views/templates/admin/import-*.tpl`


### Comment désinstaller "totalement" le module LaboData (GitHub uniquement) ?

1. Supprimer le module dans Prestashop
2. Supprimer les tables :
    * `PREFIX_category_labodata`
    * `PREFIX_feature_value_labodata`
    * `PREFIX_manufacturer_labodata`


### Comment télécharger l'édition PrestaShop Addons Marketplace ?

https://addons.prestashop.com/fr/performance-site/27294-labodata-fiches-produit-pharmacies-et-parapharmacies.html

