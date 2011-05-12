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
<p>
{$MODULE_TITLE}
</p>
<form name="licensingreport" method="POST" action="index.php" id="licensingreport">
<input type="hidden" name="module" value="DCEReports">
<input type="hidden" name="action" value="">
<input type="hidden" name='return_action' value="{$RETURN_ACTION}">
<input type="hidden" name='return_id' value="{$RETURN_ID}">

<table width="100%" border="0" cellspacing="0" cellpadding="0" class="edit view">
<tr>
<td>
    <table border="0" cellspacing="0" cellpadding="0" width="100%">
        <tr>
            <td scope="row">
                {$MOD.LBL_START_DATE}: <span class="required">*</span>
            </td>
            <td >
				<input autocomplete="off" type="text" name="startDate_date" id="startDate_date" value="{$DEFAULT_START_DATE}" size="11" maxlength="10" tabindex="1">
				{sugar_getimage name="jscalendar" ext=".gif" alt="{$mod_strings.LBL_JS_CALENDAR}" other_attributes='align="absmiddle" border="0" id="startDate_trigger" '}
				&nbsp;(<span class="dateFormat">{$USER_DATEFORMAT}</span>)
				<script type="text/javascript" language="javascript">
					Calendar.setup ({ldelim}
					inputField : "startDate_date",
					daFormat : "{$CALENDAR_FORMAT}",
					button : "startDate_trigger",
					singleClick : true,
					dateStr : "{$DEFAULT_START_DATE}",
					step : 1,
					weekNumbers:false
					{rdelim}
					);
				</script>
            </td>
        </tr>
        <tr>
            <td scope="row">
                {$MOD.LBL_END_DATE}: <span class="required">*</span>
            </td>
	            <td >
	            <input autocomplete="off" type="text" name="endDate_date" id="endDate_date" value="{$DEFAULT_END_DATE}" size="11" maxlength="10" tabindex="1">
	            {sugar_getimage name="jscalendar" ext=".gif" alt="{$mod_strings.LBL_JS_CALENDAR}" other_attributes='align="absmiddle" border="0" id="endDate_trigger" '}
	            &nbsp;(<span class="dateFormat">{$USER_DATEFORMAT}</span>)
	            <script type="text/javascript" language="javascript">
		            Calendar.setup ({ldelim}
		            inputField : "endDate_date",
		            daFormat : "{$CALENDAR_FORMAT}",
		            button : "endDate_trigger",
		            singleClick : true,
		            dateStr : "{$DEFAULT_END_DATE}",
		            step : 1,
		            weekNumbers:false
		            {rdelim}
		            );
	            </script>
            </td>
        </tr>
        <tr>
            <td scope="row">
                {$MOD.LBL_INSTANCES_TYPES}: <span class="required">*</span>
            </td>
            <td >
	            <select id="instances_types_opt[]" name="instances_types_opt[]" multiple="1" size="3">
	            {html_options options=$INSTANCES_TYPES_OPT selected=$INSTANCES_TYPES_SELECTED}
	            </select>
            </td>
        </tr>
    </table>


</td>
</tr>
</table>
<table width="100%" cellpadding="0" cellspacing="0" border="0">
<tr>
    <td align="left">
        <input title="{$MOD.LBL_RUN}" accessKey="" class="button" type="button" name="button" value="  {$MOD.LBL_RUN}  " id="btnrun"><img id="loading_img" alt="{$mod_strings.LBL_LOADING}" width="17" height="17" src="themes/default/ext/resources/images/default/shared/large-loading.gif" style="display:none">
    </td>
</tr>
</table>

</form>
<br>
<div id='listview'></div>
{$JAVASCRIPT}
