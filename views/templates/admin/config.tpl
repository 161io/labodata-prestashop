{*
 * LaboData for Prestashop
 *
 * @author 161 SARL <contact@161.io>
 * @copyright (c) 161 SARL, https://161.io
 * @license https://161.io
 *}


{if $message_content}
  <div class="alert {if $message_error}alert-danger{else}alert-success{/if}">
    {$message_content}
  </div>
{/if}

<form method="post" class="form-horizontal" id="config-form">
  <div class="panel">
    <div class="panel-heading"><i class="icon-gear"></i> {l s='Configuration' mod='labodata'}</div>
    <div class="panel-body">
      <div class="form-wrapper">
        <div class="form-group">
            <label for="config-input-email" class="control-label col-lg-3">{l s='Adresse e-mail' mod='labodata'}<sup class="text-danger">*</sup></label>
          <div class="col-lg-9">
            <input type="email" id="config-input-email" name="MOD_LABODATA_EMAIL" value="{$MOD_LABODATA_EMAIL|escape:'html':'UTF-8'}" autocomplete="off" required="required" placeholder="@" class="form-control"/>
            <span id="emailMessage"> </span>
          </div>
        </div>
        <div class="form-group">
          <label for="config-input-key" class="control-label col-lg-3">{l s='Clé API' mod='labodata'}<sup class="text-danger">*</sup></label>
          <div class="col-lg-9">
            <input type="text" id="config-input-key" name="MOD_LABODATA_KEY" value="{$MOD_LABODATA_KEY|escape:'html':'UTF-8'}" autocomplete="off" required="required" class="form-control"/>
            <span id="keyMessage"> </span>
          </div>
        </div>
        <div class="text-center">
          <a href="https://www.labodata.fr/user" target="_blank">{l s='Vous n\'avez pas de compte LaboData ?' mod='labodata'}</a>
        </div>
      </div>
    </div>
    <div class="panel-footer">
      <button type="submit" name="submit_{$module_name}" class="btn btn-default pull-right"{if !$account} disabled="disabled"{/if}>
        <i class="process-icon-save"></i> {l s='Enregistrer' mod='labodata'}
      </button>

      {if !$account}
        <p class="text-danger">{l s='Merci de remplir les champs ci-dessus pour utiliser LaboData.' mod='labodata'}</p>
      {/if}
      {if $account.credit}
        <p class="text-success">
          {l s='Vous êtes connecté en tant que :' mod='labodata'}<br/>
          {$account.firstname|escape:'html':'UTF-8'} {$account.lastname|escape:'html':'UTF-8'} <strong>{$account.society|escape:'html':'UTF-8'}</strong>
        </p>
      {/if}
      {if $account.error}
        <p class="text-danger">
          {l s='Vous n\'êtes pas connecté!' mod='labodata'}<br/>
          {$account.error.message} <strong>{$account.error.code}</strong>
        </p>
      {/if}
    </div>
  </div>
</form>

<p>&nbsp;</p>

<div class="panel">
  <div class="panel-heading">
    <span class="pull-right hidden-xs">&copy; <a href="https://161.io" target="_blank"><strong>161</strong> SARL</a></span>
    <i class="icon-phone-sign"></i> {l s='Contacter le support technique' mod='labodata'}
    &nbsp;<a href="https://www.labodata.fr" target="_blank">www.labodata.fr</a>
  </div>

  <div class="row">
    <div class="col-xs-12 col-sm-10 col-md-11">
      <p>&bull; {l s='Vous avez découvert un bug ? Vous désirez une nouvelle fonctionnalité ?' mod='labodata'}<br/>
        <a href="https://github.com/161io/labodata-prestashop/issues" target="_blank">https://github.com/161io/labodata-prestashop/issues</a>
      </p>
      <p>&bull; {l s='Vous souhaitez contribuer à ce module ?' mod='labodata'}<br/>
        <a href="https://github.com/161io/labodata-prestashop" target="_blank">https://github.com/161io/labodata-prestashop</a>
      </p>
    </div>
    <div class="col-xs-offset-4 col-xs-4 col-sm-offset-0 col-sm-2 col-md-1">
      <a href="https://www.labodata.fr" target="_blank"><img src="{$path_uri_img}logo.png" alt="LaboData" class="img-responsive center-block" width="60" height="64"/></a>
    </div>
  </div>
</div>
