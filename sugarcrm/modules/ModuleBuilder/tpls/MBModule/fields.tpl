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
<div id='studiofields'>
<input type='button' name='addfieldbtn' value='{$mod_strings.LBL_BTN_ADDFIELD}' class='button' onclick='ModuleBuilder.moduleLoadField("");'>&nbsp;
{if $editLabelsMb=='1'}
<input type='button' name='addfieldbtn' value='{$mod_strings.LBL_BTN_EDLABELS}' class='button' onclick='ModuleBuilder.moduleLoadLabels("mb");'>
{else}
<input type='button' name='addfieldbtn' value='{$mod_strings.LBL_BTN_EDLABELS}' class='button' onclick='ModuleBuilder.moduleLoadLabels("studio");'>
{/if}

<div id="field_table"></div>

</div>

<script>
{literal}
var myConfigs = {

};

var disabledCheckboxFormatter = function(elCell, oRecord, oColumn, oData)
{
   elCell.innerHTML = "<center><input type='checkbox' disabled='true'" + (oData ? " CHECKED='true'>" : "></center>");
};

var editFieldFormatter = function(elCell, oRecord, oColumn, oData)
{
   elCell.innerHTML = "<a class='crumbLink' href='javascript:void(0)' onclick='ModuleBuilder.moduleLoadField(\"" + oData + "\");'>" + oData + "</a>";
};

var labelFormatter = function(elCell, oRecord, oColumn, oData)
{
   elCell.innerHTML = oData.replace(/\:$/, '');
};

var myColumnDefs = [
    {key:"name", label:SUGAR.language.get("ModuleBuilder", "LBL_NAME"),sortable:true, resizeable:true, formatter:"editFieldFormatter", width:150},
    {key:"label", label:SUGAR.language.get("ModuleBuilder", "LBL_DROPDOWN_ITEM_LABEL"),sortable:true, resizeable:true, formatter:"labelFormatter", width:200},
    {key:"type", label:SUGAR.language.get("ModuleBuilder", "LBL_DATA_TYPE"),sortable:true,resizeable:true, width:125},
    {key:"custom", label:SUGAR.language.get("ModuleBuilder", "LBL_HCUSTOM"),sortable:true, resizeable:false, formatter:"disabledCheckboxFormatter", width:75},
    {key:"required", label:SUGAR.language.get("ModuleBuilder", "LBL_REQUIRED"),sortable:true, resizeable:false, formatter:"disabledCheckboxFormatter", width:75},
    {key:"unified_search", label:SUGAR.language.get("ModuleBuilder", "LBL_SEARCH"),sortable:true, resizeable:false, formatter:"disabledCheckboxFormatter", width:75}
];
{/literal}

var myDataSource = new YAHOO.util.DataSource({$fieldsData});
myDataSource.responseType = YAHOO.util.DataSource.TYPE_JSARRAY;
{literal}
myDataSource.responseSchema = {fields: ["label","name","type","custom", "required", "unified_search"]};
{/literal}

YAHOO.widget.DataTable.Formatter.disabledCheckboxFormatter = disabledCheckboxFormatter;
YAHOO.widget.DataTable.Formatter.editFieldFormatter = editFieldFormatter;
YAHOO.widget.DataTable.Formatter.labelFormatter = labelFormatter;
var myDataTable = new YAHOO.widget.DataTable("field_table", myColumnDefs, myDataSource, myConfigs);

ModuleBuilder.module = '{$module->name}';
ModuleBuilder.MBpackage = '{$package->name}';
ModuleBuilder.helpRegisterByID('studiofields', 'input');
{if $package->name != 'studio'}
ModuleBuilder.helpSetup('fieldsEditor','mbDefault');
{else}
ModuleBuilder.helpSetup('fieldsEditor','default');
{/if}
</script>