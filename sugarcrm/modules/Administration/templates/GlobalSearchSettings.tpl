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

<h2 class="moduleTitle">{$MOD.LBL_GLOBAL_SEARCH_SETTINGS_TITLE}</h2>

<form name='GlobalSearchSettings' method='POST'>
<input type='hidden' name='module' value='Administration'>
<input type='hidden' name='action' value='saveglobalsearchsettings'>
<table border="0" class="actionsContainer">
<tr><td>
<input title="{$APP.LBL_SAVE_BUTTON_LABEL}" accessKey="{$APP.LBL_SAVE_BUTTON_TITLE}" type="submit" class="button" name="button" value="{$APP.LBL_SAVE_BUTTON_LABEL}" onclick="select_modules();">
<input title="{$APP.LBL_CANCEL_BUTTON_LABEL}" accessKey="{$APP.LBL_CANCEL_BUTTON_KEY}" class="button" onclick="document.UnifiedSearchAdvanced.action.value='index'; document.UnifiedSearchAdvanced.module.value='Administration';" type="submit" name="button" value="{$APP.LBL_CANCEL_BUTTON_LABEL}">
</td></tr>
</table>


<table class="edit view" border="0" cellpadding="0" cellspacing="1" width="100%">
    <tr>
    <td colspan="4">
        <table width="100%" border="0" cellspacing="0" cellpadding="0">
            <tr>
	        	<td>&nbsp;</td>
	            <td scope="row" id="chooser_search_modules_text" align="center">
	            <nobr>{$MOD.LBL_GLOBAL_SEARCH_MODULES_ALLOWED}</nobr></td>            
	            <td>&nbsp;</td>
	            <td scope="row" id="chooser_skip_modules" align="center"><nobr>{$MOD.LBL_GLOBAL_SEARCH_MODULES_NOT_ALLOWED}</nobr></td>
	            <td>&nbsp;</td>
            </tr>
            <tr>
            	<td valign="top" style="padding-right: 2px; padding-left: 2px;" align="center">
            	<a onclick="return SUGAR.tabChooser.up('search_modules','search_modules','skip_modules');"><img src="themes/default/images/uparrow_big.gif?s=fe60f15f2f73b51bf4110c3e393d8963&c=1" width="13" height="13" border="0" style="margin-bottom: 1px;" alt="" /></a><br>
                <a onclick="return SUGAR.tabChooser.down('search_modules','search_modules','skip_modules');"><img src="themes/default/images/downarrow_big.gif?s=fe60f15f2f73b51bf4110c3e393d8963&c=1" width="16" height="16" border="0" style="margin-top: 1px;" alt="" /></a>
                </td>    
                <td align="center">
                <table border="0" cellspacing=0 cellpadding="0" align="center">
                <tr>
                <td id="search_modules_td" align="center">
	                <select id="search_modules" name="search_modules[]" size="10" multiple="multiple" style="width: 150px;">
					{foreach from=$UNIFIED_SEARCH_MODULES_DISPLAY name=m key=module item=info}
						{if $info.visible}<option value="{$module}">{$info.translated}</option>{/if}
					{/foreach}		                  
	                </select>
                </td>
            	</tr>
       			</table>
    			</td>
	            <td valign="top" style="padding-right: 2px; padding-left: 2px;" align="center">
	            <a onclick="return SUGAR.tabChooser.right_to_left('search_modules','skip_modules', '10', '10', '');"><img src="themes/default/images/leftarrow_big.gif?s=fe60f15f2f73b51bf4110c3e393d8963&c=1" width="16" height="16" border="0" style="margin-right: 1px;" alt=""/></a>
	            <a onclick="return SUGAR.tabChooser.left_to_right('search_modules','skip_modules', '10', '10');"><img src="themes/default/images/rightarrow_big.gif?s=fe60f15f2f73b51bf4110c3e393d8963&c=1" width="16" height="16" border="0" style="margin-left: 1px;" alt=""/></a>
	            </td>
            	<td id="skip_modules_td" align="center">
	                <select id="skip_modules" name="skip_modules[]" size="10" multiple="multiple" style="width: 150px;">
					{foreach from=$UNIFIED_SEARCH_MODULES_DISPLAY name=m key=module item=info}
						{if !$info.visible}<option value="{$module}">{$info.translated}</option>{/if}
					{/foreach}	
	                </select>
                </td>
        		<td valign="top" style="padding-right: 2px; padding-left: 2px;" align="center">
        	</tr>
    	</table>
   </td>
   <td width="90%" valign="top"><BR>&nbsp;</td>
   </td>
   </tr>
</table>


<table border="0" class="actionsContainer">
<tr><td>
<input title="{$APP.LBL_SAVE_BUTTON_LABEL}" accessKey="{$APP.LBL_SAVE_BUTTON_TITLE}" type="submit" class="button" name="button" value="{$APP.LBL_SAVE_BUTTON_LABEL}" onclick="select_modules();">
<input title="{$APP.LBL_CANCEL_BUTTON_LABEL}" accessKey="{$APP.LBL_CANCEL_BUTTON_KEY}" class="button" onclick="document.UnifiedSearchAdvanced.action.value='index'; document.UnifiedSearchAdvanced.module.value='Administration';" type="submit" name="button" value="{$APP.LBL_CANCEL_BUTTON_LABEL}">
</td></tr>
</table>
</form>

{literal}
<script type="text/javascript">
function select_modules()
{
	var search_ref = document.getElementsByTagName('select')[0];
	for(i=0; i < search_ref.options.length ;i++)
	{
	    search_ref.options[i].selected = true;
	}

	var skip_ref = document.getElementsByTagName('select')[1];
	for(x=0; x < skip_ref.options.length ;x++)
	{
	    skip_ref.options[x].selected = true;
	}
}
</script>
{/literal}