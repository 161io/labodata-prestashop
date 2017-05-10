{* Copyright (c) 161 SARL, https://161.io *}

<div class="panel">
  <div class="btn-group">
    {foreach $types as $type}
      <a href="{$type_link}{$type.name}" class="btn btn-default{if $type.name == $type_selected} active{/if}">{$type.title_fr}</a>
    {/foreach}
  </div>
  <button class="btn btn-primary pull-right" id="btn-add-all" data-confirm="{l s='Etes-vous sûr de vouloir ajouter toutes les catégories manquantes ci-dessous ?' mod='labodata'}">
    <i class="icon-flash"></i> {l s='Ajouter tout'}
  </button>
  <div class="text-muted"><br/>{l s='Avant d\'importer votre premier produit, nous vous conseillons d\'ajouter toutes les marques et toutes les catégories dont vous avez besoin.' mod='labodata'}</div>
</div>

<script>
var LaboDataTranslate={
  errorTitle:"{l s='Erreur de chargement' mod='labodata'}",
  errorMessage:"{l s='Veuillez recharger la page.' mod='labodata'}"
};
</script>
