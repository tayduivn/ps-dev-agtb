{*
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise End User
 * License Agreement ("License") which can be viewed at
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
 * by SugarCRM are Copyright (C) 2004-2006 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
*}
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000"></div>
{literal}
<script>
addForm('popup_form');
</script>
{/literal}

<form name='popup_form' method="POST" id='popup_form_id' submit="return false;" enctype="multipart/form-data">
<input type='hidden' name='module' value='ModuleBuilder'>
<input type='hidden' name='action' value='saveHook'>
<input type='hidden' name='to_pdf' value='1'>
{if empty($hookdata)}
<input type='hidden' name='newhook' value='1'>
{else}
<input type='hidden' name='type' value='{$type}'>
<input type='hidden' name='hook' value='{$hook}'>
{/if}
<input type='hidden' name='view_module' value='{$module->name}'>
{if isset($package->name)}
    <input type='hidden' name='view_package' value='{$package->name}'>
{/if}
<input type='hidden' name='is_update' value='true'>
	    {literal}
	     <input type='button' class='button' name='lsavebtn' value='{/literal}{$mod_strings.LBL_BTN_SAVE}{literal}' onclick='if(check_form("popup_form")){ModuleBuilder.submitForm("popup_form_id")};'>
	    {/literal}
		 {literal}
	        &nbsp;<input type='button' class='button' name='cancel' value='{/literal}{$mod_strings.LBL_BTN_CANCEL}{literal}' onclick='ModuleBuilder.tabPanel.get("activeTab").close()'>
	        {/literal}
{if !empty($hookdata)}
	    {literal}
	     &nbsp;<input type='button' class='button' name='ldelbtn' value='{/literal}{$mod_strings.LBL_BTN_DELETE}{literal}' onclick='if(check_form("popup_form")){this.form.action.value="DeleteHook";ModuleBuilder.submitForm("popup_form_id")};'>
	    {/literal}
{/if}
<hr>

<table width="400px" >
<tr>
<td>File: </td>
{if empty($hookdata)}
<td>    <input type="hidden" name="MAX_FILE_SIZE" value="100000" /><input name="file" type="file"></td>
{else}
<td>{$hookdata[2]}</td>
{/if}
</tr>
<tr>
<td>Type: </td>
{if empty($hookdata)}
<td>{html_options name="type" id="type"  values=$hook_types output=$hook_types}</td>
{else}
<td>{$type}</td>
{/if}
</tr>
<tr>
<td>Order: </td>
<td><input name="order" value="{$hookdata[0]}"></td>
</tr>
<tr>
<td>Class: </td>
<td><input name="class"  value="{$hookdata[3]}"></td>
</tr>
<tr>
<td>Function: </td>
<td><input name="func"  value="{$hookdata[4]}"></td>
</tr>
</table>

</form>
