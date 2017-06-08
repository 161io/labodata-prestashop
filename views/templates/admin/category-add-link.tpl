{*
 * LaboData for Prestashop
 *
 * @author 161 SARL <contact@161.io>
 * @copyright (c) 161 SARL, https://161.io
 * @license https://161.io
 *}

{if $link}
<a href="#" data-action="{$link|escape:'html':'UTF-8'}">
  <i class="icon-plus"></i> {l s='Ajouter' mod='labodata'}
</a>
{else}
<a href="#" disabled="disabled">
  <i class="icon-plus"></i> {l s='Ajouter' mod='labodata'}
</a>
{/if}
