{*
 * LaboData for Prestashop
 *
 * @author 161 SARL <contact@161.io>
 * @copyright (c) 161 SARL, https://161.io
 * @license https://161.io
 *}

<div class="panel">
  <div class="panel-heading"><i class="icon-search"></i> {l s='Effectuer une cherche dans LaboData' mod='labodata'}</div>
  <div class="panel-body">
    <div class="row">
      <div class="col-sm-1 hidden-xs">
        <a href="https://www.labodata.com" target="_blank"><img src="{$path_uri_img|escape:'html':'UTF-8'}logo.png" alt="LaboData" class="img-responsive" width="60" height="64"/></a>
      </div>
      <div class="col-sm-11 text-right">
        <span class="hidden-xs">{l s='Raccourcis :' mod='labodata'} &nbsp;</span>
        <a class="btn btn-default" href="{$labodata_redirect_autoconnect|escape:'html':'UTF-8'}" target="_blank">
          <i class="icon-user"></i><span class="hidden-xs hidden-sm"> {l s='Accéder à mon compte' mod='labodata'}</span>
        </a>
        <a class="btn btn-success" href="{$labodata_redirect_autopay|escape:'html':'UTF-8'}" target="_blank" id="labodata-autopay">
          <i class="icon-credit-card"></i> <span class="hidden-xs hidden-sm">{l s='Approvisionner mon compte' mod='labodata'}</span><span class="visible-xs-inline visible-sm-inline">{l s='Approvisionner' mod='labodata'}</span>
        </a>
        <a class="btn btn-default" href="{$labodata_redirect_autopay|escape:'html':'UTF-8'}" target="_blank">
          <span class="hidden-xs">{l s='Crédit dispo :' mod='labodata'} </span><span id="labodata-credit">{$labodata_credit|escape:'html':'UTF-8'}</span><span id="labodata-currency">{$labodata_currency}</span>
        </a>
      </div>
    </div>
    <br/>
    <form method="get" class="row">
      <div class="form-group">
        <select name="brand">
          <option value="">- {l s='Sélectionner une marque' mod='labodata'} -</option>
            {foreach $brands as $brand}
              <option value="{$brand.id|escape:'html':'UTF-8'}"{if $brand.id == $form_brand} selected="selected"{/if}>{$brand.title_fr|escape:'html':'UTF-8'} [{$brand.length|escape:'html':'UTF-8'}]</option>
            {/foreach}
        </select>
      </div>
      <div class="form-group" id="queryGroup">
        <div class="input-group">
          <input type="search" name="q" placeholder="{l s='Rechercher un produit, un médicament, un code cip...' mod='labodata'}" value="{$form_q|escape:'html':'UTF-8'}" autofocus="autofocus"/>
          <input type="hidden" name="order" value="{$form_order|escape:'html':'UTF-8'}"/>
          <input type="hidden" name="p" value="1"/>
          <input type="hidden" name="controller" value="{$form_controller|escape:'html':'UTF-8'}"/>
          <input type="hidden" name="token" value="{$form_token|escape:'html':'UTF-8'}"/>
          <div class="input-group-btn">
            <button type="button" class="btn btn-default dropdown-toggle" tabindex="-1" data-toggle="dropdown">
              <span class="visible-xs-inline">{l s='Tri' mod='labodata'}</span>
              <span class="hidden-xs">{l s='Trier par' mod='labodata'}</span>
              <strong class="order-value-text">{if 'title-asc' == $form_order}{l s='ABC' mod='labodata'}{elseif 'date-desc' == $form_order}{l s='Date' mod='labodata'}{/if}</strong>
              <i class="caret"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-order">
              <li{if 'title-asc' == $form_order} class="active"{/if}><a href="#" data-value="title-asc" data-label="{l s='ABC' mod='labodata'}">{l s='Ordre alphabétique' mod='labodata'}</a></li>
              <li{if 'date-desc' == $form_order} class="active"{/if}><a href="#" data-value="date-desc" data-label="{l s='Date' mod='labodata'}">{l s='Dernières mises à jour' mod='labodata'}</a></li>
            </ul>
            <button type="submit" class="btn btn-primary"><i class="icon-search"></i><span class="hidden-xs"> {l s='Rechercher' mod='labodata'}</span></button>
          </div>
        </div>
      </div>
    </form>
  </div>
</div>


{if $products}

<div class="panel" id="labodata-result" data-url-import="{$labodata_url_import|escape:'html':'UTF-8'}">
  <table class="table table-striped">
    <thead>
      <tr>
        <th class="text-center"><i class="icon-camera"></i> {l s='Photo' mod='labodata'}</th>
        <th>
          <strong>{l s='Marque' mod='labodata'}</strong> - {l s='Titre' mod='labodata'}<br/>
          <span class="text-muted">{l s='Descriptif' mod='labodata'}</span>
          <code>{l s='EAN/CIP' mod='labodata'}</code>
        </th>
        <th style="width:140px">
          <div class="btn-group" id="labodata-import-group">
            <button class="btn btn-primary" title="{l s='Importer toutes les photos' mod='labodata'}" data-type="image">
              <i class="icon-camera"></i>
            </button>
            <button class="btn btn-primary" title="{l s='Importer tous les descriptifs' mod='labodata'}" data-type="content">
              <i class="icon-file-text"></i>
            </button>
            <button class="btn btn-primary" title="{l s='Importer l\'ensemble des photos et des descriptifs' mod='labodata'}" data-type="full">
              <i class="icon-camera"></i> + <i class="icon-file-text"></i>
            </button>
          </div>
        </th>
      </tr>
    </thead>
    <tbody>
    {foreach $products as $product}
      <tr data-product="{$product.id|escape:'html':'UTF-8'}">
        <td><img src="{$product.image|escape:'html':'UTF-8'}" alt="labodata" class="img-responsive center-block"/></td>
        <td>
          <strong>{$product.brand.title_fr|escape:'html':'UTF-8'}</strong> - <span data-prod-title="">{$product.title_fr|escape:'html':'UTF-8'}</span><br/>
          <span class="text-muted">{$product.content_fr|escape:'html':'UTF-8'}</span><br/>
          <code>{$product.code|escape:'html':'UTF-8'}</code>
        </td>
        <td>
          <div class="btn-group">
            <button class="btn {if $product.purchase.image}btn-success{else}btn-default{/if}"
                    title="{if $product.purchase.image}{l s='Vous avez déjà acquis ces photos' mod='labodata'}{else}{l s='Acquérir les photos' mod='labodata'} ({$labodata_cost.image|escape:'html':'UTF-8'}{$labodata_currency}){/if}"
                    data-type="image"
                    data-credit="{if $product.purchase.image}0{else}{$labodata_cost.image|escape:'html':'UTF-8'}{/if}"
                    data-init-credit="{$labodata_cost.image|escape:'html':'UTF-8'}">
              <i class="icon-camera"></i>
            </button>
            <button class="btn {if $product.purchase.content}btn-success{else}btn-default{/if}"
                    title="{if $product.purchase.content}{l s='Vous avez déjà acquis ces descriptifs' mod='labodata'}{else}{l s='Acquérir les descriptifs' mod='labodata'} ({$labodata_cost.content|escape:'html':'UTF-8'}{$labodata_currency}){/if}"
                    data-type="content"
                    data-credit="{if $product.purchase.content}0{else}{$labodata_cost.content|escape:'html':'UTF-8'}{/if}"
                    data-init-credit="{$labodata_cost.content|escape:'html':'UTF-8'}">
              <i class="icon-file-text"></i>
            </button>
            <button class="btn {if $product._purchaseFull}btn-success{else}btn-default{/if}"
                    title="{if $product._purchaseFull}{l s='Vous avez déjà acquis cette fiche' mod='labodata'}{else}{l s='Acquérir les photos et les descriptifs' mod='labodata'} ({$product._purchaseFullCredit|escape:'html':'UTF-8'}{$labodata_currency}){/if}"
                    data-type="full"
                    data-credit="{if $product._purchaseFull}0{else}{$product._purchaseFullCredit|escape:'html':'UTF-8'}{/if}"
                    data-init-credit="{$labodata_cost.full|escape:'html':'UTF-8'}">
              <i class="icon-camera"></i> + <i class="icon-file-text"></i>
            </button>
          </div>
          <div>&nbsp;</div><div class="label label-state">&nbsp;</div>
        </td>
      </tr>
    {/foreach}
    </tbody>
  </table>
  <div class="text-center">
    <ul class="pagination">
      {foreach $pagination as $link}
        <li{if $link.active} class="active"{/if}><a href="{$link.href|escape:'html':'UTF-8'}">{$link.label}</a></li>
      {/foreach}
    </ul>
  </div>
</div>

{include './catalog-modals.tpl'}

<script>
var LaboDataTranslate={
  stateWait:"{l s='En attente' mod='labodata'}",
  stateProgress:"{l s='En cours' mod='labodata'}",
  stateDone:"{l s='Terminé' mod='labodata'}",
  importGroupProgress:"{l s='Une importation par lot est en cours, merci de patienter...' mod='labodata'}",
  importGroupDone:"{l s='Importation terminée avec succès' mod='labodata'}",
  importGroupExit:"{l s='Une importation par lot est en cours. Voulez-vous quitter la page ?' mod='labodata'}",
  errorInternal:"{l s='Une erreur internet s\'est produite, merci de recharger la page' mod='labodata'}"
};
</script>

{else}

<div class="panel text-center" id="labodata-result">
  <h2>{l s='Votre cherche n\'a retourné aucun résultat...' mod='labodata'}</h2>
  <p><br/>{l s='N\'hésitez pas à nous contacter directement sur le site LaboData, si vous recherchez des produits en particulier.' mod='labodata'}</p>
  <p><br/><a href="{$labodata_redirect_autoconnect|escape:'html':'UTF-8'}" class="btn btn-default btn-lg" target="_blank">{l s='Accéder au site LaboData' mod='labodata'}</a></p>
</div>

{/if}
