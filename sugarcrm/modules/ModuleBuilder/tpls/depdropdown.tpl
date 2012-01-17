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
<script type="text/javascript" src="cache/include/javascript/sugar_grp_yui_widgets.js?c=1"></script>
<style>
.edit .yui-dt table, .edit .yui-dt td, .edit .yui-dt tr th, .edit .yui-dt-liner {ldelim}
	padding: 1px 0px 1px 0 !important
{rdelim}
.edit tr.yui-dt-rec {ldelim}
    border-left-width: 0px;
    border-right-width: 0px;
{rdelim}

</style>
<table class="edit view" style="margin-left: auto;margin-right: auto;">
<tr>
	<td>Parent Value</td>
	<td>{html_options name="parent" id="parentVal" options=$parent_list_options multi=true}</td>
</tr>
<!--
<tr>
	<td>Child Values</td>
	<td>{html_options name="child" id="childValues" options=$child_list_options multiple=true}</td>
</tr>
-->
<tr><td colspan="2"><div id="childTable"></div></td></tr>
</table>

{literal}
<script type="text/javascript">
SUGAR.util.doWhen("YAHOO.SUGAR != null", function()
{
	var mapping = { };

	var childOptions = {/literal}{$childOptions}{literal};

	var ct = SUGAR.childValuesTable = new YAHOO.SUGAR.SelectionGrid(
		"childTable",
		[{key:"value", width: 200, sortable: false, hidden:true},
		 {key:"label", width: 200, sortable: false, label: "Availible Options"}],
		new YAHOO.util.LocalDataSource(childOptions, {
			responseSchema: {
			   resultsList : "options",
			   fields : [{key : "value"}, {key : "label"}]
			}
		}),
		{
			height: "200px",
			forceMulti : true
		}
	);

    var updateMapping = function(e, o, r)
    {
        var parent = YAHOO.util.Dom.get("parentVal"),
            k = parent.value,
            vals = [],
            rows = ct.getSelectedRows();

        for(var i = 0; i < rows.length; i++)
        {
            vals[i] = ct.getRecord(rows[i]).getData().value;
        }
        mapping[k] = vals;
    }
    ct.subscribe("rowSelectEvent",updateMapping);
    ct.subscribe("rowUnselectEvent",updateMapping);

    var setChildValues = function(values) {
        ct.unselectAllRows();
        var rSet = ct.getRecordSet().getRecords();
        for (var i = 0; i < rSet.length; i++)
        {
            var rec = rSet[i];
            if (values.indexOf(rec.getData().value) > -1)
            {
                ct.selectRow(rec);
            }
        }
    }

	YAHOO.util.Event.addListener("parentVal", "change", function(e){
		var parent = YAHOO.util.Dom.get("parentVal");
		var k = parent.value;
        setChildValues(mapping[k] || []);
	});


});
</script>
{/literal}