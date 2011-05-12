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

// $Id: phone.tpl 56676 2010-11-09 21:33:32Z clee $

*}


{include file="modules/DynamicFields/templates/Fields/Forms/coreTop.tpl"}
<tr>
	<td class='mbLBL'>{sugar_translate module="DynamicFields" label="COLUMN_TITLE_DEFAULT_VALUE"}:</td>
	<td>
	{if $hideLevel < 5}
		<input type='text' name='default' id='default' value='{$vardef.default}' maxlength='{$vardef.len|default:50}'>
	{else}
		<input type='hidden' id='default' name='default' value='{$vardef.default}'>{$vardef.default}
	{/if}
	</td>
</tr>
<tr>
	<td class='mbLBL'>{sugar_translate module="DynamicFields" label="COLUMN_TITLE_MAX_SIZE"}:</td>
	<td>
	{if $hideLevel < 5}
		<input type='text' name='len' id='field_len' value='{$vardef.len|default:25}' onchange="forceRange(this,1,255);changeMaxLength(document.getElementById('default'),this.value);">
		<input type='hidden' id="orig_len" name='orig_len' value='{$vardef.len}'>
		{if $action=="saveSugarField"}
		  <input type='hidden' name='customTypeValidate' id='customTypeValidate' value='{$vardef.len|default:25}' 
		      onchange="if (parseInt(document.getElementById('field_len').value) < parseInt(document.getElementById('orig_len').value)) return confirm(SUGAR.language.get('ModuleBuilder', 'LBL_CONFIRM_LOWER_LENGTH')); return true;" > 
		{/if}
		{literal}
		<script>
		function forceRange(field, min, max){
			field.value = parseInt(field.value);
			if(field.value == 'NaN')field.value = max;
			if(field.value > max) field.value = max;
			if(field.value < min) field.value = min;
		}
		function changeMaxLength(field, length){
			field.maxLength = parseInt(length);
			field.value = field.value.substr(0, field.maxLength);
		}
		</script>
		{/literal}
	{else}
		<input type='hidden' name='len' value='{$vardef.len}'>{$vardef.len}
	{/if}
	</td>
</tr>
{* //BEGIN SUGARCRM flav=een ONLY *}
{include file="modules/DynamicFields/templates/Fields/Forms/coreCalculated.tpl"}
{* //END SUGARCRM flav=een ONLY *}

{{* //BEGIN SUGARCRM flav=int ONLY*}}
<tr>
<td class='mbLBL'>{sugar_translate module="DynamicFields" label="COLUMN_TITLE_VALIDATE_US_FORMAT"}:</td>
<td>
<input type="checkbox" name="validate_usa_format" value="1" {if !empty($vardef.validate_usa_format) }CHECKED{/if} {if $hideLevel > 5}disabled{/if}/>
{if $hideLevel > 5}<input type="hidden" name="validate_usa_format" value="{$vardef.validate_usa_format}">{/if}
{sugar_getimage alt="{$mod_strings.LBL_HELP}" name="helpInline" ext=".gif" other_attributes='id="validatePhoneToolTipIcon" '}
<script type="text/javascript">
	if (!ModuleBuilder.validatePhoneToolTip)
	     ModuleBuilder.validatePhoneToolTip = new YAHOO.widget.Tooltip("validatePhoneToolTip", {ldelim}
		    context:"validatePhoneToolTipIcon", text:SUGAR.language.get("ModuleBuilder", "LBL_POPHELP_VALIDATE_US_PHONE")
		 {rdelim});
    else
	    ModuleBuilder.validatePhoneToolTip.cfg.setProperty("context", "validatePhoneToolTipIcon");
</script>
</td>
</tr>
{{* //END SUGARCRM flav=int ONLY*}}


{include file="modules/DynamicFields/templates/Fields/Forms/coreBottom.tpl"}