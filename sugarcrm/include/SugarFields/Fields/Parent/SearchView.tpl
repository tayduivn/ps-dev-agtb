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
<select name='{{$vardef.type_name}}' tabindex="{{$tabindex}}" id='{{$vardef.type_name}}' title='{{$vardef.help}}' 
onchange='document.{{$form_name}}.{{sugarvar key='name'}}.value="";document.{{$form_name}}.parent_id.value=""; 
	{{$vardef.type_name}}changeQS(); checkParentType(document.{{$form_name}}.parent_type.value, document.{{$form_name}}.btn_{{sugarvar key='name'}});'>
{html_options options={{sugarvar key='options' string=true}} selected=$fields.parent_type.value}
</select>
{{if $displayParams.split}}
<br>
{{/if}}
{if empty({{sugarvar key='options' string=true}}[$fields.parent_type.value])}
	{assign var="keepParent" value = 0}
{else}
	{assign var="keepParent value = 1}
{/if}
<input type="text" name="{{sugarvar key='name'}}" id="{{sugarvar key='name'}}" class="sqsEnabled" tabindex="{{$tabindex}}" size="{{$displayParams.size}}" value="{{sugarvar key='value'}}" autocomplete="off"><input type="hidden" name="{{$vardef.id_name}}" id="{{$vardef.id_name}}"  {if $keepParent}value="{{sugarvar memberName='vardef.id_name' key='value'}}"{/if}>
<span class="id-ff multiple">
<button type="button" name="btn_{{sugarvar key='name'}}" tabindex="{{$tabindex}}" title="{$APP.LBL_SELECT_BUTTON_TITLE}" 
	   accessKey="{$APP.LBL_SELECT_BUTTON_KEY}" class="button{{if empty($displayParams.selectOnly)}} firstChild{{/if}}" value="{$APP.LBL_SELECT_BUTTON_LABEL}" 
	   onclick='open_popup(document.{{$form_name}}.parent_type.value, 600, 400, "", true, false, {{$displayParams.popupData}}, "single", true);'>{sugar_getimage alt=$app_strings.LBL_ID_FF_SELECT name="id-ff-select" ext=".png" other_attributes=''}</button>
{{if empty($displayParams.selectOnly)}}
<button type="button" name="btn_clr_{{sugarvar key='name'}}" tabindex="{{$tabindex}}" title="{$APP.LBL_CLEAR_BUTTON_TITLE}" accessKey="{$APP.LBL_CLEAR_BUTTON_KEY}" class="button lastChild" onclick="this.form.{{sugarvar key='name'}}.value = ''; this.form.{{sugarvar key='id_name'}}.value = '';" value="{$APP.LBL_CLEAR_BUTTON_LABEL}">{sugar_getimage alt=$app_strings.LBL_ID_FF_CLEAR name="id-ff-clear" ext=".png" other_attributes=''}</button>
{{/if}}
</span>
<script type="text/javascript">
function {{$vardef.type_name}}changeQS() {ldelim}
	new_module = document.forms["{{$form_name}}"].elements["parent_type"].value;
	if(typeof(disabledModules[new_module]) != 'undefined') {ldelim}
		sqs_objects["{{$form_name}}_{{sugarvar key='name'}}"]["disable"] = true;
		document.forms["{{$form_name}}"].elements["{{sugarvar key='name'}}"].readOnly = true;
	{rdelim} else {ldelim}
		sqs_objects["{{$form_name}}_{{sugarvar key='name'}}"]["disable"] = false;
		document.forms["{{$form_name}}"].elements["{{sugarvar key='name'}}"].readOnly = false;
	{rdelim}	
	sqs_objects["{{$form_name}}_{{sugarvar key='name'}}"]["modules"] = new Array(new_module);
    enableQS(false);
{rdelim}
</script>
{literal}
{{$displayParams.disabled_parent_types}}
{/literal}