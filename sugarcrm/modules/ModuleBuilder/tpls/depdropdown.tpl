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

<script type="text/javascript" src="{sugar_getjspath file='cache/include/javascript/sugar_grp1_jquery.js'}"></script>

<style>
{literal}
#visGridWindow .yui-dt table, #visGridWindow .yui-dt td, .yui-dt tr th, #visGridWindow .yui-dt-liner {
	padding: 1px 0px 1px 0 !important
}

#visGridWindow tr.yui-dt-rec {
    border-left-width: 0px;
    border-right-width: 0px;
}

#visGridWindow tr td{
    vertical-align: top;
}

#visGridWindow ul.ddd_table{
    padding: 5px;
    margin: 0px 10px 10px 10px;
    border: solid 1px grey;
    background-color: #F8F8F8;
    min-width: 120px;
    min-height: 20px;
}

#visGridWindow ul li {
    list-style-type: none;
    margin: 3px;
    padding: 2px;
}

#visGridWindow ul li.title {
    font-weight: bold;
    font-size: 16px;
    float:left;
    top: -30px;
    position: relative;
}

#visGridWindow h3.title {
    margin-left: auto;
    margin-right: auto;
    width: 90%;
    font-weight: bold;
    text-align: center;
    color:black;
}

.dd_title{
    margin-left: auto;
    margin-right: auto;
    text-align: center;
}

#ddd_delete span {
    border: 1px solid white;
    border-radius: 5px;
    width:48px;
    height:48px;
}
#ddd_delete.drophover span{
    border-color: gray;
}

{/literal}
</style>
<div class="dd_title" style="width:600px">
    Drag items from the list of availible options on the left to one of the lists on the right to make that option availible when the given parent option is selected.
</div>
<div style="float:left;">
    <div class="dd_title">
        <div id="ddd_delete">{sugar_image name=Delete width=48 height=48 id="ddd_delete"}</div><br/>
        Availible Options
    </div>
    <div style="max-height: 450px; overflow-y: auto; overflow-x: hidden">
        <ul id="childTable" style="float:left" class="ddd_table">
            {foreach from=$child_list_options key=val item=label}
                {if $val==""}
                    {assign var=val value='--blank--'}
                    {assign var=label value='--blank--'}
                {/if}
                <li class="ui-state-default" val="{$val}">{$label}</li>
            {/foreach}
        </ul>
    </div>
</div>
<div style="max-height: 510px; overflow-y: auto; overflow-x: hidden">
<table ><tr>
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
            <ul id="ddd_{$val}_list" class="ddd_table ddd_parent_option" >
                {foreach from=$mapping.$val key=iv item=il name=parentElLoop}
                    <li class="ui-state-default" val="{$il}">{$iv}{$il}{$child_list_options.$il}</li>
                {/foreach}
            </ul>
        </td>
    {/foreach}
    </tr></table>
</div>
<div style="position: absolute;right: 10px;bottom: 10px;">
    <button onclick="ModuleBuilder.visGridWindow.hide();">
        {sugar_translate label="LBL_BTN_CANCEL" module="ModuleBuilder"}
    </button>
    <button onclick="$('#visibility_grid').val($.toJSON(SUGAR.ddd.getMapping()));ModuleBuilder.visGridWindow.hide();">
    {sugar_translate label="LBL_BTN_SAVE" module="ModuleBuilder"}
    </button>
</div>
{*</td></tr>
</table>
*}
{literal}
<script type="text/javascript">
SUGAR.ddd = {};
SUGAR.util.doWhen("typeof($) != 'undefined'", function()
{
    //Load the jQueryUI CSS
    $('<link>', {

        rel: 'stylesheet',
        type: 'text/css',
        href: 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css'
    }).appendTo('head');

    var mapping = { };
    {/literal}
    var parentOptions = {$parentOptions};
    var childOptions = {$childOptions};
    //Load from the field if its on the page
    var targetId = "{$smarty.request.targetId}";
    {literal}
    if ($("#" + targetId).length > 0)
    {
        var data = $.parseJSON($("#" + targetId).val());
        if (data && data.values)
            mapping = data.values;
    }
    //Initizalize the grids if mapping wasn't empty
    var p = $("#childTable");
    for(var i in mapping)
    {
        var vals = mapping[i];
        var l = $("#ddd_" + i + "_list");
        for(var j = 0; j < vals.length; j++)
        {
            var c  = p.children("li[val=" + vals[j] + "]");
            l.append(c.clone());
        }
    }

    //Disable text selection
    $("#visGridWindow").disableSelection();

    //Create a custom sortable list that prevents duplicate drops
    var listContainsItem = function(list, val)
    {
        var c = list.children("li[val=" + val + "]");
        return c.length != 0;
    }

    $.widget("ui.sugardddlist", $.extend({}, $.ui.sortable.prototype, {
        //Override the rearrange function to prevent drags into the availible option list or duplicate options into a list
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
        scope: "ddd_table",
        type: "semi-dynamic", //Semi-dynamic will prevent reordering within this list
        helper: function(ev, el){
            return el.clone().show();
        },
        placeholder: {
            element: function(el) {
                if (el[0].id == "ddd_delete")
                    return false;
                //for the parent table, we don't hide the item, we just create a clone for dragging
                el.hide();
                SUGAR.ddd.oldPos = el.prev();
                return el.clone();
            },
            update: function(ev, el) {
                if (!ev.mouseDelayMet && $(el.context).parent()[0] != el.parent()[0]){
                    $(el.context).show();
                    el.css( "opacity", "0.5" );
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
        $( "#ddd_" + i + "_list" ).sugardddlist({
            connectWith: ".ddd_table",
            scope: "ddd_table",
            helper: "clone",
            placeholder: {
                element: function(el) {
                    //for the parent table, we don't hide the item, we just create a clone for dragging
                    el.hide();
                    return el.clone().css( "opacity", "0.5" );
                },
                update: function(ev, el) {
                    el.show();
                },
                stop: function(event, ui) {
                    console.log("stopped");
                },
                beforeStop: function(event, ui) {
                    console.log(" before stopped");
                }
            }
        }).disableSelection();
    }

    $("#ddd_delete").droppable({
        accept: ".ddd_parent_option li",
        greedy: true,
        scope: "ddd_table",
        hoverClass: 'drophover',
        drop: function (event, ui) {
            ui.draggable.parent("ul").sortable("cancel");
            ui.draggable.remove();
        }
    });

    var blank = "--blank--";
    SUGAR.ddd.getMapping = function()
    {
        var getlistValues = function(list)
        {
            var c = list.children();
            var ret = [];
            for(var i = 0; i < c.length; i++)
            {
                var v = $(c[i]).attr("val");
                if (v == blank)
                    v = "";
                ret.push(v);
            }
            return ret;
        }
        for (var i in parentOptions)
        {
            var k = i == "" ? blank : i;
            mapping[i] = getlistValues($( "#ddd_" + k + "_list" ));
        }
        return {
            trigger: $("#parent_dd").val(),
            values : mapping
        };
    }
});
</script>
{/literal}