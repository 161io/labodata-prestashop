{* Copyright (c) 161 SARL, https://161.io *}

{* TRANSLATIONS:
{l s='Marque' mod='labodata'}
{l s='Nature de produit' mod='labodata'}
{l s='Conditionnement' mod='labodata'}
{l s='Spécificité' mod='labodata'}
{l s='Label' mod='labodata'}
{l s='Indication / Contre-indication' mod='labodata'}
{l s='Principe actif' mod='labodata'}
*}

<div class="panel">
  <div class="btn-group">
    {foreach $types as $type}
      <a href="{$type_link}{$type.name}" class="btn btn-default{if $type.name == $type_selected} active{/if}">{l s=$type.title_fr mod='labodata'}</a>
    {/foreach}
  </div>
  <button class="btn btn-primary pull-right" id="btn-add-all" data-confirm="{l s='Etes-vous sûr de vouloir ajouter toutes les éléments manquants ci-dessous ?' mod='labodata'}">
    <i class="icon-flash"></i> {l s='Ajouter tout' mod='labodata'}
  </button>
  <div class="text-muted"><br/>{l s='Avant d\'importer votre premier produit, nous vous conseillons d\'ajouter toutes les marques et toutes les caractéristiques dont vous avez besoin.' mod='labodata'}</div>
</div>

<script>
var LaboDataTranslate={
  errorTitle:"{l s='Erreur de chargement' mod='labodata'}",
  errorMessage:"{l s='Veuillez recharger la page.' mod='labodata'}"
};
</script>
