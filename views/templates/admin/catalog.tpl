{* Copyright (c) 161 SARL, https://161.io *}

<div class="panel">
  <div class="panel-heading"><i class="icon-search"></i> {l s='Effectuer une cherche dans LaboData' mod='labodata'}</div>
  <div class="panel-body">
    <div class="row">
      <div class="col-sm-1 hidden-xs">
        <a href="https://www.labodata.fr" target="_blank"><img src="{$path_uri_img}logo.png" alt="LaboData" class="img-responsive" width="60" height="64"/></a>
      </div>
      <div class="col-sm-11 text-right">
        <span class="hidden-xs">{l s='Raccourcis' mod='labodata'} : &nbsp;</span>
        <a class="btn btn-default" href="{$labodata_redirect_autoconnect}" target="_blank">
          <i class="icon-user"></i><span class="hidden-xs hidden-sm"> {l s='Accéder à mon compte' mod='labodata'}</span>
        </a>
        <a class="btn btn-success" href="{$labodata_redirect_autopay}" id="labodata-autopay">
          <i class="icon-credit-card"></i> <span class="hidden-xs hidden-sm">{l s='Approvisionner mon compte' mod='labodata'}</span><span class="visible-xs-inline visible-sm-inline">{l s='Approvisionner' mod='labodata'}</span>
        </a>
        <a class="btn btn-default" href="{$labodata_redirect_autopay}">
          <span class="hidden-xs">{l s='Crédit dispo' mod='labodata'} : </span><span id="labodata-credit">{$labodata_credit}</span><span id="labodata-currency">{$labodata_currency}</span>
        </a>
      </div>
    </div>
    <br/>
    <form method="get" class="row">
      <div class="form-group">
        <select name="brand">
          <option value="">- {l s='Sélectionner une marque' mod='labodata'} -</option>
            {foreach $brands as $brand}
              <option value="{$brand.id}"{if $brand.id == $form_brand} selected="selected"{/if}>{$brand.title_fr} [{$brand.length}]</option>
            {/foreach}
        </select>
      </div>
      <div class="form-group" id="queryGroup">
        <div class="input-group">
          <input type="search" name="q" placeholder="{l s='Rechercher un produit, un médicament, un code cip...' mod='labodata'}" value="{$form_q|escape:'html':'UTF-8'}" autofocus="autofocus"/>
          <input type="hidden" name="p" value="1"/>
          <input type="hidden" name="controller" value="{$form_controller}"/>
          <input type="hidden" name="token" value="{$form_token}"/>
          <div class="input-group-btn">
            <button type="submit" class="btn btn-primary"><i class="icon-search"></i><span class="hidden-xs"> {l s='Rechercher' mod='labodata'}</span></button>
          </div>
        </div>
      </div>
    </form>
  </div>
</div>


{if $products}

<div class="panel" id="labodata-result" data-url-import="{$labodata_url_import}">
  <table class="table table-striped">
    <thead>
      <tr>
        <th class="text-center"><i class="icon-camera"></i> {l s='Photo' mod='labodata'}</th>
        <th>
          <strong>{l s='Brand' mod='labodata'}</strong> - {l s='Titre' mod='labodata'}<br/>
          <span class="text-muted">{l s='Descriptif' mod='labodata'}</span>
          <code>{l s='EAN/CIP' mod='labodata'}</code>
        </th>
        <th style="width:140px">&nbsp;</th>
      </tr>
    </thead>
    <tbody>
    {foreach $products as $product}
      <tr data-product="{$product.id}">
        <td><img src="{$product.image}" alt="labodata" class="img-responsive center-block"/></td>
        <td>
          <strong>{$product.brand.title_fr}</strong> - <span data-prod-title="">{$product.title_fr}</span><br/>
          <span class="text-muted">{$product.content_fr}</span><br/>
          <code>{$product.code}</code>
        </td>
        <td>
          <div class="btn-group">
            <button class="btn {if $product.purchase.image}btn-success{else}btn-default{/if}"
                    title="{if $product.purchase.image}{l s='Vous avez déjà acquis ces photos'}{else}{l s='Acquérir les photos'} ({$labodata_cost.image}{$labodata_currency}){/if}"
                    data-type="image"
                    data-credit="{if $product.purchase.image}0{else}{$labodata_cost.image}{/if}">
              <i class="icon-camera"></i>
            </button>
            <button class="btn {if $product.purchase.content}btn-success{else}btn-default{/if}"
                    title="{if $product.purchase.content}{l s='Vous avez déjà acquis ces descriptifs'}{else}{l s='Acquérir les descriptifs'} ({$labodata_cost.content}{$labodata_currency}){/if}"
                    data-type="content"
                    data-credit="{if $product.purchase.content}0{else}{$labodata_cost.content}{/if}">
              <i class="icon-file-text"></i>
            </button>
            <button class="btn {if $product._purchaseFull}btn-success{else}btn-default{/if}"
                    title="{if $product._purchaseFull}{l s='Vous avez déjà acquis cette fiche'}{else}{l s='Acquérir les photos et les descriptifs'} ({$product._purchaseFullCredit}{$labodata_currency}){/if}"
                    data-type="full"
                    data-credit="{if $product._purchaseFull}0{else}{$product._purchaseFullCredit}{/if}">
              <i class="icon-camera"></i> + <i class="icon-file-text"></i>
            </button>
          </div>
          <div>&nbsp;</div><div class="label label-state" data-msg-progress="{l s='En cours' mod='labodata'}" data-msg-done="{l s='Terminé' mod='labodata'}">&nbsp;</div>
        </td>
      </tr>
    {/foreach}
    </tbody>
  </table>
  <div class="text-center">
    <ul class="pagination">
      {foreach $pagination as $link}
        <li{if $link.active} class="active"{/if}><a href="{$link.href}">{$link.label}</a></li>
      {/foreach}
    </ul>
  </div>
</div>

{include './catalog-modals.tpl'}

{else}

<div class="panel text-center" id="labodata-result">
  <h2>{l s='Votre cherche n\'a retrouné aucun résultat...' mod='labodata'}</h2>
  <p><br/>{l s='N\'hésitez pas à nous contacter directement sur le site LaboData, si vous recherchez des produits en particulier.' mod='labodata'}</p>
  <p><br/><a href="{$labodata_redirect_autoconnect}" class="btn btn-default btn-lg" target="_blank">{l s='Accéder au site LaboData.fr' mod='labodata'}</a></p>
</div>

{/if}
