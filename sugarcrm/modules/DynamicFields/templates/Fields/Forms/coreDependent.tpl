{*
//FILE SUGARCRM flav=pro ONLY
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

// $Id: enum.tpl 35784 2008-05-20 21:31:40Z dwheeler $
*}
{* && $vardef.type != 'date' && $vardef.type != 'datetimecombo' *}
{if $vardef.type != 'enum' && $vardef.type != 'address'
 && $vardef.type != 'multienum' && $vardef.type != 'radioenum'
 && $vardef.type != 'html' && $vardef.type != 'relate'
 && $vardef.type != 'url' && $vardef.type != 'iframe' && $vardef.type != 'parent'  && $vardef.type != 'image'
 && empty($vardef.function) && (!isset($vardef.studio.calculated) || $vardef.studio.calculated != false)
}

<tr><td class='mbLBL'>{sugar_translate module="DynamicFields" label="LBL_CALCULATED"}:</td>
    <td style="line-height:1em"><input type="checkbox" name="calculated" id="calculated" value="1" onclick ="ModuleBuilder.toggleCF()"
        {if !empty($vardef.calculated) && !empty($vardef.formula)}CHECKED{/if} {if $hideLevel > 5}disabled{/if}/>
		{if $hideLevel > 5}
            <input type="hidden" name="calculated" value="{$vardef.calculated}">
        {/if}
		{sugar_help text=$mod_strings.LBL_POPHELP_CALCULATED FIXX=250 FIXY=80}
		<input type="hidden" name="enforced" id="enforced" value="{$vardef.enforced}">
    </td>
</tr>
<tr id='formulaRow' {if empty($vardef.formula)}style="display:none"{/if}>
	<td class='mbLBL'>{sugar_translate module="DynamicFields" label="LBL_FORMULA"}:</td>
    <td>
        <input id="formula" type="hidden" name="formula" value="{$vardef.formula}" onchange="document.getElementById('formula_display').value = this.value"/>
        <input id="formula_display" type="text" name="formula_display" value="{$vardef.formula}" readonly="1" style="background-color:#eee"/>
	    <input type="button" class="button"  name="editFormula" style="margin-top: -2px"
		      value="{sugar_translate label="LBL_BTN_EDIT_FORMULA"}"
            onclick="ModuleBuilder.moduleLoadFormula(YAHOO.util.Dom.get('formula').value, 'formula')"/>
    </td>
</tr>
{/if}
{if $vardef.type != 'address'}
<tr id='depCheckboxRow'><td class='mbLBL'>{sugar_translate module="DynamicFields" label="LBL_DEPENDENT"}:</td>
    <td><input type="checkbox" name="dependent" id="dependent" value="1" onclick ="ModuleBuilder.toggleDF(null, '#popup_form_id .toggleDep')"
        {if !empty($vardef.dependency)}CHECKED{/if} {if $hideLevel > 5}disabled{/if}/>
        {sugar_help text=$mod_strings.LBL_POPHELP_DEPENDENT FIXX=250 FIXY=80}
    </td>
</tr>
<tr id='visFormulaRow' {if empty($vardef.dependency)}style="display:none"{/if}><td class='mbLBL'>{sugar_translate module="DynamicFields" label="LBL_VISIBLE_IF"}:</td> 
    <td>
        <input id="dependency" type="hidden" name="dependency" value="{$vardef.dependency|escape:'html'}" onchange="document.getElementById('dependency_display').value = this.value"/>
        <input id="dependency_display" type="text" name="dependency_display" value="{$vardef.dependency|escape:'html'}" readonly="1" style="background-color:#eee"/>
          <input class="button" type=button name="editFormula" value="{sugar_translate label="LBL_BTN_EDIT_FORMULA"}" 
            onclick="ModuleBuilder.moduleLoadFormula(YAHOO.util.Dom.get('dependency').value, 'dependency', 'boolean')"/>
    </td>
</tr>

{* //BEGIN SUGARCRM flav=een ONLY *}
<tr id='enforcedRow' {if empty($vardef.enforced)}style="display:none"{/if}><td class='mbLBL'>{sugar_translate module="DynamicFields" label="LBL_ENFORCED"}:</td>
    <td><input type="checkbox" name="enforced" id="enforced" value="1" onclick="ModuleBuilder.toggleEnforced();"{if !empty($vardef.enforced)}CHECKED{/if} {if $hideLevel > 5}disabled{/if}/>
        {if $hideLevel > 5}<input type="hidden" name="enforced" value="{$vardef.enforced}">{/if}
    </td>
</tr>
{elseif $vardef.type == 'enum'}
<tr>
	<td class='mbLBL'>{sugar_translate module="DynamicFields" label="LBL_DYNAMIC_VALUES_CHECKBOX"}:</td>
	<td>
		<input type="checkbox" name="dynamic_values" value="0" {if !empty($vardef.visibility_grid)}checked{/if} onclick="toggleDisplay('{$vardef.name}_dynamic_trigger');"/>
	</td>
</tr>
<tr id='{$vardef.name}_dynamic_trigger' {if empty($vardef.visibility_grid)}style='display:none'{/if} >
	<td class='mbLBL'>{sugar_translate module="DynamicFields" label="LBL_DEPENDENT_TRIGGER"}:</td>
	<td>
		{html_options name="trigger" id="trigger" selected=$selected_trigger values=$triggers output=$triggers"}
		<input type='button' value='{sugar_translate module="DynamicFields" label="LBL_BTN_EDIT_VISIBILITY"}' class='button' onclick="ModuleBuilder.moduleLoadVisibility('{$vardef.name}' , this.form.options.value , this.form.trigger.value , 'visibility_grid' );">
		<input type='hidden' name='visibility_grid' id='visibility_grid' value='{$visiblity_grid}'>
	</td>
</tr>
{* //END SUGARCRM flav=een ONLY *}
{/if}