{*
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-enterprise-eula.html
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
 * by SugarCRM are Copyright (C) 2004-2010 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
*}
{if $smarty.request.prospect_id}
<table width="100%" cellspacing="1" cellpadding="0" border="0" class="tabForm">
<tbody>
<tr>
<th class="dataLabel" colspan="8" align="left">
<h4>Lead Company Information</h4>
</th>
</tr>
<tr>
<td width="1%">
<input type="radio" name="lead_account_radio" value="select" />
</td>
<td width="12%" valign="top" nowrap="" class="dataLabel" id="leadaccount_name_label">
Select Existing Lead Company:
<span class="required">*</span>
</td>
<td width="37%" valign="top" nowrap="" class="tabEditViewDF">
<input type="text" name="leadaccount_name" class="sqsEnabled" tabindex="l" id="leadaccount_name" size="" value="" title="" autocomplete="off"  >
<input type="hidden" name="leadaccount_id" id="leadaccount_id" value="">
<input type="button" name="btn_leadaccount_name" tabindex="l" title="Select [Alt+T]" accessKey="T" class="button" value="Select" onclick='{literal}open_popup("LeadAccounts", 600, 400, "", true, false, {"call_back_function":"set_return","form_name":"EditView","field_to_name_array":{"id":"leadaccount_id","name":"leadaccount_name"}}, "single", true);{/literal}'>
<input type="button" name="btn_clr_leadaccount_name" tabindex="l" title="Clear [Alt+C]" accessKey="C" class="button" onclick="this.form.leadaccount_name.value = \'\'; this.form.leadaccount_id.value = \'\';" value="Clear">
</td>
</tr>
<tr>
<td>&nbsp;</td>
<td>-OR-</td>
<tr>
<td width="1%">
<input type="radio" name="lead_account_radio" value="create" checked="checked"/>
</td>
<td width="12%" valign="top" nowrap="" class="dataLabel" id="new_leadaccount_name_label">
Create New Lead Company:
<span class="required">*</span>
</td>
<td width="37%" valign="top" nowrap="" class="tabEditViewDF">
<input type="text" name="new_leadaccount_name" tabindex="l" id="new_leadaccount_name" size="" value="{$fields.leadaccount_name.value}" title="" >
</td>
</tr>
</tbody></table>
{/if}
{{include file='include/EditView/footer.tpl'}}
{if $smarty.request.prospect_id}
{literal}
<script type="text/javascript">
<!--
Ext.onReady(function()
{
    removeFromValidate('EditView', 'leadaccount_name');
    addToValidate('EditView', 'leadaccount_name', 'relate', false,'Lead Company Name' );
});
-->
</script>
{/literal}
{/if}
