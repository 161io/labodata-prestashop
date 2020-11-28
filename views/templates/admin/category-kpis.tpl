{*
 * LaboData for Prestashop
 *
 * @author 161 SARL <contact@161.io>
 * @copyright (c) 161 SARL, https://161.io
 * @license https://161.io
 *}


{* TRANSLATIONS:
{l s='Marque' mod='labodata'}
{l s='Nature de produit' mod='labodata'}
{l s='Conditionnement' mod='labodata'}
{l s='Spécificité' mod='labodata'}
{l s='Label' mod='labodata'}
{l s='Indication / Contre-indication' mod='labodata'}
{l s='Principe actif' mod='labodata'}

{l s='Parapharmacie' mod='labodata'}
{l s='Médicaments' mod='labodata'}
*}

<div class="panel">
  <div class="btn-group">
    {foreach $types as $type}
      <a href="{$type_link|escape:'html':'UTF-8'}{$type.name|escape:'html':'UTF-8'}" class="btn btn-default{if $type.name == $type_selected} active{/if}">{l s=$type.title mod='labodata'}</a>
    {/foreach}
  </div>
  <button class="btn btn-primary pull-right" id="btn-add-all" data-confirm="{l s='Etes-vous sûr de vouloir ajouter toutes les éléments manquants ci-dessous ?' mod='labodata'}">
    <i class="icon-flash"></i> {l s='Ajouter tout' mod='labodata'}
  </button>
  <div class="text-muted"><br/>{l s='Avant d\'importer votre premier produit, nous vous conseillons d\'ajouter les marques, les caractéristiques et les catégories dont vous avez besoin.' mod='labodata'}</div>
</div>

<script>
var LaboDataTranslate={
  errorTitle:"{l s='Erreur de chargement' mod='labodata'}",
  errorMessage:"{l s='Veuillez recharger la page.' mod='labodata'}"
};
</script>
