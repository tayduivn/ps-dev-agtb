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
*}

{capture name=idname assign=idname}{sugar_variable_constructor objectName=$parentFieldArray memberName=$vardef.name key='name'}{/capture}
{$displayParams.preCode}
<input name="{$idname}" id="{$idname}" type='text'  size='10' maxlength='10' value='{sugar_variable_constructor objectName=$parentFieldArray memberName=$vardef.name key='value'}' >
<img src="themes/{$theme}/images/jscalendar.gif" alt='Enter Date'  id="{$idname}_trigger" align='absmiddle'> <span class='dateFormat'>{$CAL_DATE_FORMAT}</span>
<script type="text/javascript">
Calendar.setup ({ldelim}ldelim{rdelim}
inputField : "{$idname}",
ifFormat : "{$CAL_DATE_FORMAT}",
daFormat : "{$CAL_DATE_FORMAT}",
button : "{$idname}_trigger",
singleClick : true,
dateStr : "{sugar_variable_constructor objectName=$parentFieldArray memberName=$vardef.name key='value'}",
step : 1,
weekNumbers:false
{ldelim}rdelim{rdelim}
);
</script>
{$displayParams.postCode}