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
{if strlen({{sugarvar key='value' string=true}}) <= 0}
{assign var="value" value={{sugarvar key='default_value' string=true}} }
{else}
{assign var="value" value={{sugarvar key='value' string=true}} }
{/if}

{{if empty($displayParams.idName)}}
{assign var="id" value={{sugarvar key='name' string=true}} }
{{else}}
{assign var="id" value={{$displayParams.idName}} }
{{/if}}


<script type='text/javascript'>
function {$id}_range_change(val) 
{ldelim}
  calculate_between = (val == 'between');

  //Clear the values depending on the operation
  if(calculate_between) {ldelim}
     document.getElementById("range_{$id}").value = '';   
  {rdelim} else {ldelim}
     document.getElementById("start_range_{$id}").value = '';
     document.getElementById("end_range_{$id}").value = '';
  {rdelim}
 
  document.getElementById("{$id}_range_div").style.display = calculate_between ? 'none' : '';
  document.getElementById("{$id}_between_range_div").style.display = calculate_between ? '' : 'none';
{rdelim}
</script>

<span style="white-space:nowrap !important;">
<select id="{$id}_range_choice" name="{$id}_range_choice" style="width:125px !important;" onchange="{$id}_range_change(this.value);">
<option value="equals">{$APP.LBL_EQUAL}</option>
<option value="not_equal">{$APP.LBL_NOT_EQUAL}</option>
<option value="greater_than">{$APP.LBL_GREATER_THAN}</option>
<option value="greater_than_equals">{$APP.LBL_GREATER_THAN_OR_EQUAL}</option>
<option value="less_than">{$APP.LBL_LESS_THAN}</option>
<option value="less_than_equals">{$APP.LBL_LESS_THAN_OR_EQUAL}</option>
<option value="between">{$APP.LBL_BETWEEN}</option>
</select>
<div id="{$id}_range_div" style="display:'';">
<input type='text' name='range_{$id}' id='range_{$id}' style='width:75px !important;' size='{{$displayParams.size|default:20}}' 
    {{if isset($displayParams.maxlength)}}maxlength='{{$displayParams.maxlength}}'{{/if}} 
    value='{$smarty.request.{{$id_range}} }' title='{{$vardef.help}}' tabindex='{{$tabindex}}' {{$displayParams.field}}>
</div>
<div id="{$id}_between_range_div" style="display:none;">
<input type='text' name='start_range_{$id}' 
    id='start_range_{$id}' style='width:75px !important;' size='{{$displayParams.size|default:10}}' 
    {{if isset($displayParams.maxlength)}}maxlength='{{$displayParams.maxlength}}'{{/if}} 
    value='{$smarty.request.{{$id_range_start}} }' tabindex='{{$tabindex}}'>
{$APP.LBL_AND}
<input type='text' name='end_range_{$id}' 
    id='end_range_{$id}' style='width:75px !important;' size='{{$displayParams.size|default:10}}' 
    {{if isset($displayParams.maxlength)}}maxlength='{{$displayParams.maxlength}}'{{/if}} 
    value='{$smarty.request.{{$id_range_end}} }' tabindex='{{$tabindex}}'>    
</div> 
</span>

{if isset($smarty.request.{{$id_range_choice}}) }
<script type='text/javascript'>
YAHOO.util.Event.onDOMReady(function() {ldelim}
{$id}_range_change("{$smarty.request.{{$id_range_choice}} }");
var select = document.getElementById("{$id}_range_choice");
for(var m = 0; m < select.options.length; m++) 
{ldelim}
	if(select.options[m].value == "{$smarty.request.{{$id_range_choice}} }")
	{ldelim}
		select.options[m].selected = true;
		break;
	{rdelim}
{rdelim}
{rdelim});
</script>
{/if}