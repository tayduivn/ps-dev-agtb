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

{assign var=date_value value={{sugarvar key='value' string=true}} }
<input class="date_input" autocomplete="off" type="text" name="{{$idname}}" id="{{$idname}}" value="{$date_value}" title='{{$vardef.help}}' {{$displayParams.field}} tabindex='{{$tabindex}}' {{if !$vardef.enforced}}size="11" maxlength="10"{{/if}} >
{{if !$displayParams.hiddeCalendar && !$vardef.enforced}}
<img border="0" src="{sugar_getimagepath file='jscalendar.gif'}" alt="{$APP.LBL_ENTER_DATE}" id="{{$idname}}_trigger" align="absmiddle" />
{{/if}}
{{if $displayParams.showFormats}}
&nbsp;(<span class="dateFormat">{$USER_DATEFORMAT}</span>)
{{/if}}
{{if !$displayParams.hiddeCalendar}}
<script type="text/javascript">
Calendar.setup ({ldelim}
inputField : "{{$idname}}",
daFormat : "{$CALENDAR_FORMAT}",
button : "{{$idname}}_trigger",
singleClick : true,
dateStr : "{$date_value}",
step : 1,
weekNumbers:false
{rdelim}
);
</script>
{{/if}}