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
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/jquery-ui.js"></script>

<style>
{literal}
.edit .yui-dt table, .edit .yui-dt td, .edit .yui-dt tr th, .edit .yui-dt-liner {
	padding: 1px 0px 1px 0 !important
}
.edit tr.yui-dt-rec {
    border-left-width: 0px;
    border-right-width: 0px;
}

.edit ul.ddd_table{
    padding: 5px;
    margin: 0px 10px 10px 10px;
    border: solid 1px grey;
    background-color: #F8F8F8;
    min-width: 120px;
    min-height: 20px;
}

.edit ul li {
    list-style-type: none;
    margin: 3px;
    padding: 2px;
}

.edit ul li.title {
    font-weight: bold;
    font-size: 16px;
    float:left;
    top: -30px;
    position: relative;
}

h3.title {
    margin-left: auto;
    margin-right: auto;
    width: 90%;
    font-weight: bold;
    text-align: center;
    color:black;
}

{/literal}
</style>
<table class="edit view" style="margin-left: auto;margin-right: auto; width:900px;">
<tr>
	<td>Parent Value</td>
    <td width="100%">Children</td>
</tr>
<tr><td style="white-space: nowrap;">
    <ul id="childTable" style="float:left" class="ddd_table">
        {foreach from=$child_list_options key=val item=label}
            {if $val==""}
                {assign var=val value='--blank--'}
                {assign var=label value='&nbsp;'}
            {/if}
            <li class="ui-state-default" id="ddd_parent_{$val}" val="{$val}">{$label}</li>
        {/foreach}
    </ul>
</td><td >
    <table><tr>
    {foreach from=$parent_list_options key=val item=label name=parentloop}
        {if $smarty.foreach.parentloop.index % 4 == 0 && !$smarty.foreach.parentloop.first}
            </tr><tr>
        {/if}
        {if $val==""}
            {assign var=val value='--blank--'}
            {assign var=label value='--blank--'}
        {/if}
        <td>
            <h3 class="title">{$label}</h3>
            <ul id="ddd_{$val}_list" class="ddd_table" >

            </ul>
        </td>
    {/foreach}
    </tr></table>
</td></tr>
</table>

{literal}
<script type="text/javascript">
SUGAR.ddd = {};
SUGAR.util.doWhen("typeof($) != 'undefined'", function()
{
    $('<link>', {

        rel: 'stylesheet',
        type: 'text/css',
        href: 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css'
    }).appendTo('head');

    var mapping = { };
    var parentOptions = {/literal}{$parentOptions}{literal};
    var childOptions = {/literal}{$childOptions}{literal};

    var createList = function(id, con, items)
    {
        $.apply(window, id).sortable({
            connectWith: con
        });
        if(typeof(items) == "object")
        {

        }
    }

    //Create a custom sortable list that prevents duplicate drops
    var re = $.ui.sortable.prototype._rearrange;
    var listContainsItem = function(list, val)
    {
        var c = list.children("li[val=" + val + "]");
        return c.length != 0;
    }

    $.widget("ui.sugardddlist", $.extend({}, $.ui.sortable.prototype, {
        _rearrange: function(event, i, a, hardRefresh) {
            if(i){
                //If the target list isn't empty and contains the value we are dragging, return.
                var val = this.currentItem.attr("val");
                var p = i.item.parent();
                var c  = p.children("li[val=" + val + "]");
                if (p.attr("id") == "childTable" || (listContainsItem(p, val) && this.currentItem.parent()[0] != p[0]))
                    return true;
            }
            //Call the parent function
            return $.ui.sortable.prototype._rearrange.call(this, event, i, a, hardRefresh);
        }
    }));

    SUGAR.ddd.childTable =  $( "#childTable" ).sugardddlist({
        connectWith: ".ddd_table",
        type: "semi-dynamic", //Semi-dynamic will prevent reordering within this list
        helper: function(ev, el){
            return el.clone().show();
        },
        placeholder: {
            element: function(el) {
                //for the parent table, we don't hide the item, we just create a clone for dragging
                el.hide();
                SUGAR.ddd.oldPos = el.prev();
                return el.clone().css( "opacity", "0.5" );
            },
            update: function(ev, el) {
                if (!ev.mouseDelayMet && $(el.context).parent()[0] != el.parent()[0]){
                    $(el.context).show();
                }
                el.show();
            }
        },
        remove: function(event, ui) {
            //If the item is being removed, put a clone back in the orginal list.
            if (SUGAR.ddd.oldPos[0])
                SUGAR.ddd.oldPos.after(ui.item.clone());
            else {
                SUGAR.ddd.childTable.children().first().before(ui.item.clone());
            }
        }
    }).disableSelection();

    for (var i in parentOptions)
    {
        if (i == "") i = "--blank--";
        SUGAR.ddd.parentTable =  $( "#ddd_" + i + "_list" ).sugardddlist({
            connectWith: ".ddd_table",
            helper: "clone",
            placeholder: {
                element: function(el) {
                    //for the parent table, we don't hide the item, we just create a clone for dragging
                    el.hide();
                    return el.clone().css( "opacity", "0.5" );
                },
                update: function(ev, el) {
                    el.show();
                }
            }
        }).disableSelection();
    }


/*
    var pt = SUGAR.parentTable = new YAHOO.SUGAR.SelectionGrid(
        "parentTable",
        [{key:"value", width: 200, sortable: false, hidden:true},
         {key:"label", width: 200, sortable: false, label: "Parent Options"}],
        new YAHOO.util.LocalDataSource(parentOptions, {
            responseSchema: {
               resultsList : "options",
               fields : [{key : "value"}, {key : "label"}]
            }
        }),
        {
            height: "200px",
            forceMulti : true
    });

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
*/

});
</script>
{/literal}