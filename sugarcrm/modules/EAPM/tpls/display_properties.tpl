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

<script type="text/javascript" src="include/javascript/sugar_grp_yui_widgets.js"></script>

<form name='ExternalAccountManagement' action='index.php' method='POST'>
<input type='hidden' name='module' value='EAPM'>
<input type='hidden' name='action' value='SaveDisplayProperties'>
    <input type='hidden' name='disabled_apis' value=''>
	<table width='400' class='sources_table_div' border='0' cellspacing='1'>
     <tr style='padding-bottom: 10px'>
		<td colspan='8' nowrap>
			<input type="submit" class="button primary" value="{$APP.LBL_SAVE_BUTTON_LABEL}" onclick="SUGAR.saveGlobalSearchSettings();">&nbsp;
		</td>
	</tr>
	<tr height='5'><td></td></tr>
	<tr style='padding-top: 10px;'>
		<td colspan='8' nowrap'>
		<div id='inlineGlobalSearch' class='add_table'>
		<table id="GlobalSearchSettings" class="GlobalSearchSettings edit view" style='margin-bottom:0px;' border="0" cellspacing="0" cellpadding="0">
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


var get = YAHOO.util.Dom.get;
var enabled_modules = {$enabled_modules};
var disabled_modules = {$disabled_modules};
var lblEnabled = '{sugar_translate label="LBL_ACTIVE_MODULES" module="Administration"}';
var lblDisabled = '{sugar_translate label="LBL_DISABLED_MODULES" module="Administration"}';
{literal}

SUGAR.globalSearchEnabledTable = new YAHOO.SUGAR.DragDropTable(
	"enabled_div",
	[{key:"label",  label: lblEnabled, width: 200, sortable: false},
	 {key:"name", label: lblEnabled, hidden:true}],
	new YAHOO.util.LocalDataSource(enabled_modules, {
		responseSchema: {fields : [{key : "name"}, {key : "label"}]}
	}),
	{height: "200px"}
);

SUGAR.globalSearchDisabledTable = new YAHOO.SUGAR.DragDropTable(
	"disabled_div",
	[{key:"label",  label: lblDisabled, width: 200, sortable: false},
	 {key:"name", label: lblDisabled, hidden:true}],
	new YAHOO.util.LocalDataSource(disabled_modules, {
		responseSchema: {fields : [{key : "name"}, {key : "label"}]}
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
	var disabledTable = SUGAR.globalSearchDisabledTable;
	var apis = "";
	for(var i=0; i < disabledTable.getRecordSet().getLength(); i++){
		var data = disabledTable.getRecord(i).getData();
		if (data.name && data.name != '')
		    apis += "," + data.name;
	}
	apis = apis == "" ? apis : apis.substr(1);
	document.forms['ExternalAccountManagement'].elements['disabled_apis'].value = apis;
}
{/literal}


</script>