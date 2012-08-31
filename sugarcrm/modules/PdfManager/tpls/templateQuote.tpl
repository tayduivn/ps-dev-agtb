{*
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

*}
<table border="0" cellspacing="2">
<tbody>
<tr>
<td rowspan="4" width="180%"><img src="{$logoUrl}" alt="" /></td>
<td width="60%"><strong>{$MOD.LBL_TPL_QUOTE}</strong></td>
<td width="60%">&nbsp;</td>
</tr>
<tr>
<td bgcolor="#DCDCDC" width="75%">{$MOD.LBL_TPL_QUOTE_NUMBER}</td>
<td width="75%">{literal}{$fields.quote_num}{/literal}</td>
</tr>
<tr>
<td bgcolor="#DCDCDC" width="75%">{$MOD.LBL_TPL_SALES_PERSON}</td>
<td width="75%">{literal}{$fields.assigned_user_link.name}{/literal}</td>
</tr>
<tr>
<td bgcolor="#DCDCDC" width="75%">{$MOD.LBL_TPL_VALID_UNTIL}</td>
<td width="75%">{literal}{$fields.date_quote_expected_closed}{/literal}</td>
</tr>
</tbody>
</table>
<p>&nbsp;</p>
<table style="width: 50%;" border="0" cellspacing="2">
<tbody>
<tr style="color: #ffffff;" bgcolor="#4B4B4B">
<td>{$MOD.LBL_TPL_BILL_TO}</td>
<td>{$MOD.LBL_TPL_SHIP_TO}</td>
</tr>
<tr>
<td>{literal}{$fields.billing_contact_name}{/literal}</td>
<td>{literal}{$fields.shipping_contact_name}{/literal}</td>
</tr>
<tr>
<td>{literal}{$fields.billing_account_name}{/literal}</td>
<td>{literal}{$fields.shipping_account_name}{/literal}</td>
</tr>
<tr>
<td>{literal}{$fields.billing_address_street}{/literal}</td>
<td>{literal}{$fields.shipping_address_street}{/literal}</td>
</tr>
<tr>
<td>{literal}{$fields.billing_address_city}, {$fields.billing_address_state}, {$fields.billing_address_postalcode}{/literal}</td>
<td>{literal}{$fields.shipping_address_city}, {$fields.shipping_address_state}, {$fields.shipping_address_postalcode}{/literal}</td>
</tr>
<tr>
<td>{literal}{$fields.billing_address_country}{/literal}</td>
<td>{literal}{$fields.shipping_address_country}{/literal}</td>
</tr>
</tbody>
</table>
<p>&nbsp;</p>
<!--START_BUNDLE_LOOP-->
<h3>{literal}{$bundle.name}{/literal}</h3>
<table style="width: 100%;" border="0">
<tbody>
<tr style="color: #ffffff;" bgcolor="#4B4B4B">
<td width="70%">{$MOD.LBL_TPL_QUANTITY}</td>
<td width="175%">{$MOD.LBL_TPL_PART_NUMBER}</td>
<td width="175%">{$MOD.LBL_TPL_PRODUCT}</td>
<td width="70%">{$MOD.LBL_TPL_LIST_PRICE}</td>
<td width="70%">{$MOD.LBL_TPL_UNIT_PRICE}</td>
<td width="70%">{$MOD.LBL_TPL_EXT_PRICE}</td>
<td width="70%">{$MOD.LBL_TPL_DISCOUNT}</td>
</tr>
<!--START_PRODUCT_LOOP-->
<tr>
<td width="70%">{literal}{$product.quantity}{/literal}</td>
<td width="175%">{literal}{$product.mft_part_num}{/literal}</td>
<td width="175%">{literal}{$product.name}{/literal}</td>
<td align="right" width="70%">{literal}{$product.list_price}{/literal}</td>
<td align="right" width="70%">{literal}{$product.discount_price}{/literal}</td>
<td align="right" width="70%">{literal}{$product.ext_price}{/literal}</td>
<td align="right" width="70%">{literal}{$product.discount_amount}{/literal}</td>
</tr>
<!--END_PRODUCT_LOOP--></tbody>
</table>
<table>
<tbody>
<tr>
<td><hr /></td>
</tr>
</tbody>
</table>
<table style="width: 100%; margin: auto;" border="0">
<tbody>
<tr>
<td width="210%">&nbsp;</td>
<td width="45%">{$MOD.LBL_TPL_SUBTOTAL}</td>
<td align="right" width="45%">{literal}{$bundle.subtotal}{/literal}</td>
</tr>
<tr>
<td width="210%">&nbsp;</td>
<td width="45%">{$MOD.LBL_TPL_DISCOUNT}</td>
<td align="right" width="45%">{literal}{$bundle.deal_tot}{/literal}</td>
</tr>
<tr>
<td width="210%">&nbsp;</td>
<td width="45%">{$MOD.LBL_TPL_DISCOUNTED_SUBTOTAL}</td>
<td align="right" width="45%">{literal}{$bundle.new_sub}{/literal}</td>
</tr>
<tr>
<td width="210%">&nbsp;</td>
<td width="45%">{$MOD.LBL_TPL_TAX}</td>
<td align="right" width="45%">{literal}{$bundle.tax}{/literal}</td>
</tr>
<tr>
<td width="210%">&nbsp;</td>
<td width="45%">{$MOD.LBL_TPL_SHIPPING}</td>
<td align="right" width="45%">{literal}{$bundle.shipping}{/literal}</td>
</tr>
<tr>
<td width="210%">&nbsp;</td>
<td width="45%">{$MOD.LBL_TPL_TOTAL}</td>
<td align="right" width="45%">{literal}{$bundle.total}{/literal}</td>
</tr>
</tbody>
</table>
<p>&nbsp;</p>
<p>&nbsp;</p>
<!--END_BUNDLE_LOOP-->
<p>&nbsp;</p>
<p>&nbsp;</p>
<table>
<tbody>
<tr>
<td><hr /></td>
</tr>
</tbody>
</table>
<p>&nbsp;</p>
<table style="width: 100%; margin: auto;" border="0">
<tbody>
<tr>
<td width="200%">&nbsp;</td>
<td style="font-weight: bold;" colspan="2" align="center" width="150%"><b>{$MOD.LBL_TPL_GRAND_TOTAL}</b></td>
<td width="75%">&nbsp;</td>
<td align="right" width="75%">&nbsp;</td>
</tr>
<tr>
<td width="200%">&nbsp;</td>
<td width="75%">{$MOD.LBL_TPL_CURRENCY}</td>
<td width="75%">{literal}{$fields.currency_iso}{/literal}</td>
<td width="75%">{$MOD.LBL_TPL_SUBTOTAL}</td>
<td align="right" width="75%">{literal}{$fields.subtotal}{/literal}</td>
</tr>
<tr>
<td width="200%">&nbsp;</td>
<td width="75%">&nbsp;</td>
<td align="right" width="75%">&nbsp;</td>
<td width="75%">{$MOD.LBL_TPL_DISCOUNT}</td>
<td align="right" width="75%">{literal}{$fields.deal_tot}{/literal}</td>
</tr>
<tr>
<td width="200%">&nbsp;</td>
<td width="75%">&nbsp;</td>
<td width="75%">&nbsp;</td>
<td width="75%">{$MOD.LBL_TPL_DISCOUNTED_SUBTOTAL}</td>
<td align="right" width="75%">{literal}{$fields.new_sub}{/literal}</td>
</tr>
<tr>
<td width="200%">&nbsp;</td>
<td width="75%">{$MOD.LBL_TPL_TAX_RATE}</td>
<td width="75%">{literal}{$fields.taxrate_value}{/literal}</td>
<td width="75%">{$MOD.LBL_TPL_TAX}</td>
<td align="right" width="75%">{literal}{$fields.tax}{/literal}</td>
</tr>
<tr>
<td width="200%">&nbsp;</td>
<td width="75%">{$MOD.LBL_TPL_SHIPPING_PROVIDER}</td>
<td width="75%">{literal}{$fields.shipper_name}{/literal}</td>
<td width="75%">{$MOD.LBL_TPL_SHIPPING}</td>
<td align="right" width="75%">{literal}{$fields.shipping}{/literal}</td>
</tr>
<tr>
<td width="200%">&nbsp;</td>
<td width="75%">&nbsp;</td>
<td width="75%">&nbsp;</td>
<td width="75%">{$MOD.LBL_TPL_TOTAL}</td>
<td align="right" width="75%">{literal}{$fields.total}{/literal}</td>
</tr>
</tbody>
</table>
<p>&nbsp;</p>
<table>
<tbody>
<tr>
<td><hr /></td>
</tr>
</tbody>
</table>