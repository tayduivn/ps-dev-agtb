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

<script type="text/javascript" src="{sugar_getjspath file='include/javascript/sugar_grp_yui_widgets.js'}"></script>
<link rel="stylesheet" type="text/css" href="{sugar_getjspath file='modules/Connectors/tpls/tabs.css'}"/>

<form name='UnifiedSearchAdvancedMain' action='index.php' onsubmit="SUGAR.saveGlobalSearchSettings();" method='POST'>
<input type='hidden' name='module' value='Home'>
<input type='hidden' name='query_string' value='test'>
<input type='hidden' name='advanced' value='true'>
<input type='hidden' name='action' value='UnifiedSearch'>
<input type='hidden' name='search_form' value='false'>
<input type='hidden' name='search_modules' value=''>
<input type='hidden' name='skip_modules' value=''>
	<table width='600' class='edit view' border='0' cellspacing='1'>
	<tr style='padding-bottom: 10px'>
		<td colspan='8' nowrap>
			<input id='searchFieldMain' class='searchField' type='text' size='80' name='query_string' value='{$query_string}'>
		    <input type="submit" class="button primary" value="{$LBL_SEARCH_BUTTON_LABEL}">&nbsp;
			<a href='javascript:toggleInlineSearch()' style='color: #005A9B; text-decoration:none; font-weight: bold;'>{$MOD.LBL_SELECT_MODULES}&nbsp;
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
		<div id='inlineGlobalSearch' class='add_table'>
		<table id="GlobalSearchSettings" class="GlobalSearchSettings edit view" style='margin-bottom:0px;' border="0" cellspacing="0" cellpadding="0">
		    <tr>
		    	<td colspan="2">
		    	{sugar_translate label="LBL_SELECT_MODULES_TITLE" module="Administration"}
		    	</td>
		    </tr>
		    <tr>
				<td width='1%'>
					<div id="enabled_div"></div>	
				</td>
				<td>
					<div id="disabled_div"></div>
				</td>
			</tr>
		</table>
		</div>
		</td>
	</tr>
	</table>
</form>

<script type="text/javascript">
{literal}
function toggleInlineSearch()
{
    if (document.getElementById('inlineGlobalSearch').style.display == 'none')
    {
		SUGAR.globalSearchEnabledTable.render();
		SUGAR.globalSearchDisabledTable.render();    
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
{/literal}


var get = YAHOO.util.Dom.get;
var enabled_modules = {$enabled_modules};
var disabled_modules = {$disabled_modules};
var lblEnabled = '{sugar_translate label="LBL_ACTIVE_MODULES" module="Administration"}';
var lblDisabled = '{sugar_translate label="LBL_DISABLED_MODULES" module="Administration"}';
{literal}

SUGAR.globalSearchEnabledTable = new YAHOO.SUGAR.DragDropTable(
	"enabled_div",
	[{key:"label",  label: lblEnabled, width: 200, sortable: false},
	 {key:"module", label: lblEnabled, hidden:true}],
	new YAHOO.util.LocalDataSource(enabled_modules, {
		responseSchema: {fields : [{key : "module"}, {key : "label"}]}
	}),  
	{height: "200px"}
);

SUGAR.globalSearchDisabledTable = new YAHOO.SUGAR.DragDropTable(
	"disabled_div",
	[{key:"label",  label: lblDisabled, width: 200, sortable: false},
	 {key:"module", label: lblDisabled, hidden:true}],
	new YAHOO.util.LocalDataSource(disabled_modules, {
		responseSchema: {fields : [{key : "module"}, {key : "label"}]}
	}),
	{height: "200px"}
);

SUGAR.globalSearchEnabledTable.disableEmptyRows = true;
SUGAR.globalSearchDisabledTable.disableEmptyRows = true;
SUGAR.globalSearchEnabledTable.addRow({module: "", label: ""});
SUGAR.globalSearchDisabledTable.addRow({module: "", label: ""});
SUGAR.globalSearchEnabledTable.render();
SUGAR.globalSearchDisabledTable.render();

SUGAR.saveGlobalSearchSettings = function()
{
	var enabledTable = SUGAR.globalSearchEnabledTable;
	var modules = "";
	for(var i=0; i < enabledTable.getRecordSet().getLength(); i++){
		var data = enabledTable.getRecord(i).getData();
		if (data.module && data.module != '')
		    modules += "," + data.module;
	}
	modules = modules == "" ? modules : modules.substr(1);
	document.forms['UnifiedSearchAdvancedMain'].elements['search_modules'].value = modules;
}
{/literal}

var handleHideShow = function()
{ldelim}

{if $SHOWGSDIV == 'yes'}
	document.getElementById("inlineGlobalSearch").style.display="";
{else}
	document.getElementById("inlineGlobalSearch").style.display="none";
{/if}	

{rdelim};

YAHOO.util.Event.onDOMReady(handleHideShow); 

</script>