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
{{capture name=idname assign=idname}}{{sugarvar key='name'}}{{/capture}}
{{if !empty($displayParams.idName)}}
    {{assign var=idname value=$displayParams.idName}}
{{/if}}

{{assign var=flag_field value=$vardef.name|cat:_flag}}

<table border="0" cellpadding="0" cellspacing="0">
<tr valign="middle">
<td nowrap>
<div id="{{$idname}}_time"></div>
{{if $displayParams.showFormats}}
<span class="timeFormat">{$TIME_FORMAT}</span>
{{/if}}
</td>
</tr>
</table>
<input type="hidden" id="{{$idname}}" name="{{$idname}}" value="{$fields[{{sugarvar key='name' stringFormat=true}}].value}">
<script type="text/javascript" src="include/SugarFields/Fields/Time/Time.js"></script>
<script type="text/javascript">

//cleanup because this happens in a screwy order in a quickcreate, and the standard $(document).ready and YUI functions don't work quite right
function initTime(){ldelim}
	if(typeof(Time) != "undefined"){ldelim}
		var combo_{{$idname}} = new Time("{$fields[{{sugarvar key='name' stringFormat=true}}].value}", "{{$idname}}", "{$TIME_FORMAT}", "{{$tabindex}}");
		//Render the remaining widget fields
		var text = combo_{{$idname}}.html('{{$displayParams.updateCallback}}');
		document.getElementById('{{$idname}}_time').innerHTML = text;
		
		//addToValidateBinaryDependency('{$form_name}',"{{$idname}}_hours", 'alpha', false, "{$APP.ERR_MISSING_REQUIRED_FIELDS} {$APP.LBL_HOURS}" ,"{{$idname}}_date");
		//addToValidateBinaryDependency('{$form_name}', "{{$idname}}_minutes", 'alpha', false, "{$APP.ERR_MISSING_REQUIRED_FIELDS} {$APP.LBL_MINUTES}" ,"{{$idname}}_date");
		//addToValidateBinaryDependency('{$form_name}', "{{$idname}}_meridiem", 'alpha', false, "{$APP.ERR_MISSING_REQUIRED_FIELDS} {$APP.LBL_MERIDIEM}","{{$idname}}_date");
	
	{rdelim}
	else{ldelim}
		setTimeout(initTime, 500);
	{rdelim}
{rdelim}

initTime();

</script>

<script type="text/javascript">
function update_{{$idname}}_available() {ldelim}
      YAHOO.util.Event.onAvailable("{{$idname}}_time_hours", this.handleOnAvailable, this);
{rdelim}

update_{{$idname}}_available.prototype.handleOnAvailable = function(me) {ldelim}
	//Call update for first time to round hours and minute values
	combo_{{$idname}}.update();
{rdelim}

var obj_{{$idname}} = new update_{{$idname}}_available();
</script>