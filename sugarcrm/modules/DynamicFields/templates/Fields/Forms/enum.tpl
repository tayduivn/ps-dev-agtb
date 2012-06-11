{*

/**
 * LICENSE: The contents of this file are subject to the SugarCRM Professional
 * End User License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You
 * may not use this file except in compliance with the License.  Under the
 * terms of the license, You shall not, among other things: 1) sublicense,
 * resell, rent, lease, redistribute, assign or otherwise transfer Your
 * rights to the Software, and 2) use the Software for timesharing or service
 * bureau purposes such as hosting the Software for commercial gain and/or for
 * the benefit of a third party.  Use of the Software may be subject to
 * applicable fees and any use of the Software without first paying applicable
 * fees is strictly prohibited.  You do not have the right to remove SugarCRM
 * copyrights from the source code or user interface.
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
 * by SugarCRM are Copyright (C) 2006 SugarCRM, Inc.; All Rights Reserved.
 */

// $Id: enum.tpl 56676 2010-05-25 21:33:32Z dwheeler $

*}

 {include file="modules/DynamicFields/templates/Fields/Forms/coreTop.tpl"}

<tr>
	<td class='mbLBL'>{sugar_translate module="DynamicFields" label="LBL_DROP_DOWN_LIST"}:</td>
	<td>
	{if $hideLevel < 5 && empty($vardef.function)}
		{html_options name="options" id="options" selected=$selected_dropdown values=$dropdowns output=$dropdowns onChange="ModuleBuilder.dropdownChanged(this.value);"}{if !$uneditable}<br><input type='button' value='{sugar_translate module="DynamicFields" label="LBL_BTN_EDIT"}' class='button' onclick="ModuleBuilder.moduleDropDown(this.form.options.value, this.form.options.value);">&nbsp;<input type='button' value='{sugar_translate module="DynamicFields" label="LBL_BTN_ADD"}' class='button' onclick="ModuleBuilder.moduleDropDown('', this.form.name.value);">{/if}
	{else}
		<input type='hidden' name='options' value='{$selected_dropdown}'>{$selected_dropdown}
	{/if}
	</td>
</tr>
<tr>
	<td class='mbLBL'>{sugar_translate module="DynamicFields" label="COLUMN_TITLE_DEFAULT_VALUE"}:</td>
	<td>
	{if $hideLevel < 5 && empty($vardef.function)}
		{html_options name="default[]" id="default[]" selected=$selected_options options=$default_dropdowns multiple=$multi}
	{else}
		<input type='hidden' name='default[]' id='default[]' value='$vardef.default'>{$vardef.default}
	{/if}
	</td>
</tr>
<tr>
	<td class='mbLBL' >{sugar_translate module="DynamicFields" label="COLUMN_TITLE_MASS_UPDATE"}:</td>
	<td>
	{if $hideLevel < 5}
		<input type="checkbox" id="massupdate"  name="massupdate" value="1" {if !empty($vardef.massupdate)}checked{/if}/>
	{else}
		<input type="checkbox" id="massupdate"  name="massupdate" value="1" disabled {if !empty($vardef.massupdate)}checked{/if}/>
	{/if}
	</td>
</tr>
{* //BEGIN SUGARCRM flav=pro ONLY *}
{if !$multi && !$radio}
<tr id='depTypeRow' class="toggleDep"><td class='mbLBL'>{sugar_translate module="DynamicFields" label="LBL_DEPENDENT"}:</td>
    <td>
        <select id="depTypeSelect" onchange="ModuleBuilder.toggleParent(this.value == 'parent'); ModuleBuilder.toggleDF(this.value == 'formula'); ">
            <option label="{sugar_translate module="ModuleBuilder" label="LBL_NONE"}" value="">{sugar_translate module="ModuleBuilder" label="LBL_NONE"}</option>
            {if !empty($module_dd_fields)}
                <option label="{sugar_translate module="ModuleBuilder" label="LBL_PARENT_DROPDOWN"}" value="parent">{sugar_translate module="ModuleBuilder" label="LBL_PARENT_DROPDOWN"}</option>
            {/if}
            <option label="{sugar_translate module="ModuleBuilder" label="LBL_FORMULA"}" value="formula">{sugar_translate module="ModuleBuilder" label="LBL_FORMULA"}</option>
        </select>
        <script>
			//For enums, don't use the formal dependent checkbox, use this dependency type selector
            $('#depCheckboxRow').hide();
            ModuleBuilder.toggleParent({if empty($vardef.visibility_grid)}false{else}true{/if});
            {if !empty($vardef.visibility_grid)}
                $('#depTypeSelect').val("parent");
            {elseif !empty($vardef.dependency)}
                $('#depTypeSelect').val("formula");
            {/if}
		</script>
        {** We can only have a formula or a vis_grid. Before we save we need to clear the one we aren't using **}
        <input type="hidden" id="customTypeValidate" onchange="return ModuleBuilder.validateDD()" />
    </td>
</tr>
<tr id='visGridRow' {if empty($vardef.visibility_grid)}style="display:none"{/if} class="toggleDep">
    <td class='mbLBL'>{sugar_translate module="DynamicFields" label="LBL_PARENT_DROPDOWN"}:</td>
	<td>
        {html_options name="parent_dd" id="parent_dd" selected=$vardef.visibility_grid.trigger options=$module_dd_fields}
        {php}$this->_tpl_vars['visgridJSON'] = empty($this->_tpl_vars['vardef']['visibility_grid']) ? "" : json_encode($this->_tpl_vars['vardef']['visibility_grid']){/php}
        <input type="hidden" name="visibility_grid" id="visibility_grid" value='{$visgridJSON}'/>
	{if $hideLevel < 5}
        <button onclick="ModuleBuilder.editVisibilityGrid('visibility_grid', YAHOO.util.Dom.get('parent_dd').value, YAHOO.util.Dom.get('options').value)">
            {sugar_translate module="DynamicFields" label="LBL_EDIT_VIS"}
        </button>
	{/if}
	</td>
</tr>
{/if}
{* //END SUGARCRM flav=pro ONLY *}
{include file="modules/DynamicFields/templates/Fields/Forms/coreBottom.tpl"}