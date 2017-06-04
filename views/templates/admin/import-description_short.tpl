{*
 * LaboData for Prestashop
 *
 * @author 161 SARL <contact@161.io>
 * @copyright (c) 161 SARL, https://161.io
 * @license https://161.io
 *}

{* Ce template vous permet de definir le formatage de la zone de texte "description_short" dans un produit. *}

{$description_short|truncate:800:"..."}

{*
{foreach $descriptions as $item}
<h2>{$item.title}</h2>
{$item.content}
{if !$item@last}<p>&nbsp;</p>{/if}
{/foreach}
*}
