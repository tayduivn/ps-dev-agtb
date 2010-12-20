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
{{if empty($displayParams.idName)}}
{assign var="id" value={{sugarvar key='name' string=true}} }
{{else}}
{assign var="id" value={{$displayParams.idName}} }
{{/if}}

<div style="white-space:nowrap !important;">
<select id="{$id}_range_choice" name="{$id}_range_choice" style="width:125px !important;" onchange="{$id}_range_change(this.value);">
<option value="equals">{$APP.LBL_ON}</option>
<option value="not_equal">{$APP.LBL_NOT_ON}</option>
<option value="greater_than">{$APP.LBL_AFTER}</option>
<option value="less_than">{$APP.LBL_BEFORE}</option>
<option value="last_7_days">{$APP.LBL_LAST_7_DAYS}</option>
<option value="next_7_days">{$APP.LBL_NEXT_7_DAYS}</option>
<option value="last_30_days">{$APP.LBL_LAST_30_DAYS}</option>
<option value="next_30_days">{$APP.LBL_NEXT_30_DAYS}</option>
<option value="last_month">{$APP.LBL_LAST_MONTH}</option>
<option value="this_month">{$APP.LBL_THIS_MONTH}</option>
<option value="next_month">{$APP.LBL_NEXT_MONTH}</option>
<option value="last_year">{$APP.LBL_LAST_YEAR}</option>
<option value="this_year">{$APP.LBL_THIS_YEAR}</option>
<option value="next_year">{$APP.LBL_NEXT_YEAR}</option>
<option value="between">{$APP.LBL_BETWEEN}</option>
</select>
<div id="{$id}_range_div" style="white-space:nowrap !important, display:'';">
<input autocomplete="off" type="text" name="range_{$id}" id="range_{$id}" value='{$smarty.request.{{$id_range}} }' title='{{$vardef.help}}' {{$displayParams.field}} tabindex='{{$tabindex}}' size="11" maxlength="10" style="width:100px !important;">
{{if !$displayParams.hiddeCalendar}}
<img border="0" src="{sugar_getimagepath file='jscalendar.gif'}" alt="{$APP.LBL_ENTER_DATE}" id="{$id}_trigger" align="absmiddle"/>
{{/if}}
{{if $displayParams.showFormats}}
&nbsp;(<span class="dateFormat">{$USER_DATEFORMAT}</span>)
{{/if}}
{{if !$displayParams.hiddeCalendar}}
<script type="text/javascript">
Calendar.setup ({ldelim}
inputField : "range_{$id}",
daFormat : "{$CALENDAR_FORMAT}",
button : "{$id}_trigger",
singleClick : true,
dateStr : "{$date_value}",
step : 1,
weekNumbers:false
{rdelim}
);
</script>
{{/if}}    
</div>

<div id="{$id}_between_range_div" style="display:none;">
{assign var=date_value value={{sugarvar key='value' string=true}} }
<input autocomplete="off" type="text" name="start_range_{$id}" id="start_range_{$id}" value='{$smarty.request.{{$id_range_start}} }' title='{{$vardef.help}}' {{$displayParams.field}} tabindex='{{$tabindex}}' size="11" style="width:100px !important;" maxlength="10">
{{if !$displayParams.hiddeCalendar}}
<img border="0" src="{sugar_getimagepath file='jscalendar.gif'}" alt="{$APP.LBL_ENTER_DATE}" id="start_range_{$id}_trigger" align="absmiddle" />
{{/if}}
{{if $displayParams.showFormats}}
&nbsp;(<span class="dateFormat">{$USER_DATEFORMAT}</span>)
{{/if}}
{{if !$displayParams.hiddeCalendar}}
<script type="text/javascript">
Calendar.setup ({ldelim}
inputField : "start_range_{$id}",
daFormat : "{$CALENDAR_FORMAT}",
button : "start_range_{$id}_trigger",
singleClick : true,
dateStr : "{$date_value}",
step : 1,
weekNumbers:false
{rdelim}
);
</script>
{{/if}} 
{$APP.LBL_AND}
{assign var=date_value value={{sugarvar key='value' string=true}} }
<input autocomplete="off" type="text" name="end_range_{$id}" id="end_range_{$id}" value='{$smarty.request.{{$id_range_end}} }' title='{{$vardef.help}}' {{$displayParams.field}} tabindex='{{$tabindex}}' size="11" style="width:100px !important;" maxlength="10">
{{if !$displayParams.hiddeCalendar}}
<img border="0" src="{sugar_getimagepath file='jscalendar.gif'}" alt="{$APP.LBL_ENTER_DATE}" id="end_range_{$id}_trigger" align="absmiddle" />
{{/if}}
{{if $displayParams.showFormats}}
&nbsp;(<span class="dateFormat">{$USER_DATEFORMAT}</span>)
{{/if}}
{{if !$displayParams.hiddeCalendar}}
<script type="text/javascript">
Calendar.setup ({ldelim}
inputField : "end_range_{$id}",
daFormat : "{$CALENDAR_FORMAT}",
button : "end_range_{$id}_trigger",
singleClick : true,
dateStr : "{$date_value}",
step : 1,
weekNumbers:false
{rdelim}
);
</script>
{{/if}} 
</div>
</div>


<script type='text/javascript'>

function {$id}_range_change(val) 
{ldelim}
  if(val == 'between') {ldelim}
     document.getElementById("range_{$id}").value = '';  
     document.getElementById("{$id}_range_div").style.display = 'none';
     document.getElementById("{$id}_between_range_div").style.display = ''; 
  {rdelim} else if (val == 'equals' || val == 'not_equal' || val == 'greater_than' || val == 'less_than') {ldelim}
     if((/^\[.*\]$/).test(document.getElementById("range_{$id}").value))
     {ldelim}
     	document.getElementById("range_{$id}").value = '';
     {rdelim}
     document.getElementById("start_range_{$id}").value = '';
     document.getElementById("end_range_{$id}").value = '';
     document.getElementById("{$id}_range_div").style.display = '';
     document.getElementById("{$id}_between_range_div").style.display = 'none';
  {rdelim} else {ldelim}
     document.getElementById("range_{$id}").value = '[' + val + ']';    
     document.getElementById("start_range_{$id}").value = '';
     document.getElementById("end_range_{$id}").value = ''; 
     document.getElementById("{$id}_range_div").style.display = 'none';
     document.getElementById("{$id}_between_range_div").style.display = 'none';         
  {rdelim}
{rdelim}

var {$id}_range_reset = function()
{ldelim}
{$id}_range_change('equals');
{rdelim}

YAHOO.util.Event.onDOMReady(function() {ldelim}
if(document.getElementById('search_form_clear'))
{ldelim}
YAHOO.util.Event.addListener('search_form_clear', 'click', {$id}_range_reset);
{rdelim}

{if isset($smarty.request.{{$id_range_choice}}) }

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

{/if}

{rdelim});
</script>