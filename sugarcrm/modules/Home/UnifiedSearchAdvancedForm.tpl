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

// $Id: UnifiedSearchAdvancedForm.tpl 43629 2009-01-27 18:29:39Z jmertic $

*}

<br/>
<form name='UnifiedSearchAdvancedMain' action='index.php' method='POST'>
<input type='hidden' name='module' value='Home'>
<input type='hidden' name='query_string' value='test'>
<input type='hidden' name='advanced' value='true'>
<input type='hidden' name='action' value='UnifiedSearch'>
<input type='hidden' name='search_form' value='false'>
	<table width='600' class='edit view' border='0' cellspacing='1'>
	<tr style='padding-bottom: 10px'>
		<td colspan='8' nowrap>
			<input id='searchFieldMain' class='searchField' type='text' size='80' name='query_string' value='{$query_string}'>
		    &nbsp;<input type="submit" class="button" value="{$LBL_SEARCH_BUTTON_LABEL}" onclick="select_modules();">&nbsp;
			<a class='tabFormAdvLink' href='javascript:toggleInlineSearch()'>
			{if $SHOWGSDIV == 'yes'}
			<img src='{sugar_getimagepath file="basic_search.gif"}' id='up_down_img' border=0>
			{else}
			<img src='{sugar_getimagepath file="advanced_search.gif"}' id='up_down_img' border=0>
			{/if}
			</a>
			<input type='hidden' id='showGSDiv' name='showGSDiv' value='{$SHOWGSDIV}'>
		</td>
	</tr>
	<tr height='5'><td></td></tr>
	<tr style='padding-top: 10px;'>
		<td colspan='8' nowrap'>
		<div id='inlineGlobalSearch' {if $SHOWGSDIV == 'yes'}style='display:"";'{else}style='display:none;'{/if}>
		<table class="edit view" border="0" cellpadding="0" cellspacing="1">
		    <tr>
		    <td colspan="4">
		        <table border="0" cellspacing="0" cellpadding="0">
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
							{foreach from=$MODULES_TO_SEARCH name=m key=module item=info}
								{if $info.checked}<option value="{$module}">{$info.translated}</option>{/if}
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
							{foreach from=$MODULES_TO_SEARCH name=m key=module item=info}
								{if !$info.checked}<option value="{$module}">{$info.translated}</option>{/if}
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
		</div>
		</td>
	</tr>
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


function toggleInlineSearch()
{
    if (document.getElementById('inlineGlobalSearch').style.display == 'none')
    {
        document.getElementById('showGSDiv').value = 'yes'		
        document.getElementById('inlineGlobalSearch').style.display = '';
{/literal}	
        document.getElementById('up_down_img').src='{sugar_getimagepath file="basic_search.gif"}';
{literal}
    }else{
{/literal}			
        document.getElementById('up_down_img').src='{sugar_getimagepath file="advanced_search.gif"}';
{literal}			
        document.getElementById('showGSDiv').value = 'no';		
        document.getElementById('inlineGlobalSearch').style.display = 'none';		
    }
}
</script>
{/literal}