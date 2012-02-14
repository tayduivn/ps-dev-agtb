{literal}
<style type="text/css">

.yui-ac-content {
width:70%;
}

</style>
{/literal}
<script type="text/javascript" src="cache/include/javascript/sugar_grp_yui_widgets.js"></script>
<link rel="stylesheet" type="text/css" href="{sugar_getjspath file='modules/Connectors/tpls/tabs.css'}"/>

{if (!$smarty.get.ajax)}
<h2>{$APP.LBL_SEARCH_RESULTS}</h2>
<br>

<div id='ftsSearchBarContainer' >
    <div id="ftsAutoCompleteResult" style="width:100%;!important"></div>
    <input type="text" placeholder="{$APP.LBL_SEARCH}" name="ftsSearchField" id="ftsSearchField" value="{$smarty.request.q}"  style="width: 70%!important" >
    <input type="button" class="button primary"value="{$APP.LBL_SEARCH}" onclick="SUGAR.FTS.search();">
    <a class='tabFormAdvLink' href='javascript:SUGAR.FTS.toggleAdvancedOptions();'>
        <span id='advanced_search_img_span'>
            {sugar_getimage alt=$alt_show_hide name="advanced_search" ext=".gif" other_attributes='border="0" id="advanced_search_img" '}
        </span>
        <span id='basic_search_img_span' style="display:none;">
            {sugar_getimage alt=$alt_show_hide name="basic_search" ext=".gif" other_attributes='border="0" id="basic_search_img" '}
        </span>
    </a>
</div>
<div><span id='totalCount'>{$totalHits}</span> {$APP.LBL_SEARCH_RESULTS_FOUND} (<span id='totalTime' style="font-style: italic;">{$totalTime}</span>{$APP.LBL_SEARCH_RESULTS_TIME})</div>
    <br><br>

    <div id='inlineGlobalSearch' style="display:none;">
        <form method="POST" onsubmit="SUGAR.FTS.saveModuleFilterSettings();" >
            <input type="hidden" name="module" value="Users">
            <input type="hidden" name="action" value="saveftsmodules">
            <input type="hidden" name="disabled_modules" value="" id="disabled_modules">

        <table id="GlobalSearchSettings" class="GlobalSearchSettings edit view" style='margin-bottom:0px;' border="0" cellspacing="0" cellpadding="0" width="30%">
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
            <tr>
                <td colspan="2">&nbsp;</td>
            </tr>
            <tr>
                <td colspan="2"><input type="submit" class="button primary" value="{$APP.LBL_SAVE_BUTTON_LABEL}">&nbsp;</td>
            </tr>
        </table>
        </form>
    </div>

{/if}


<table width="50%">
<tr ><td width="15%">&nbsp;</td><td width="90%"></td></tr>
<tr valign="top" >
    <td id="moduleListTD" style="">
        <b>{$APP.LBL_MODULE_FILTER}</b>
        {include file='include/MVC/View/tpls/fts_modfilter.tpl'}
    </td>
<td>
    <div id="sugar_full_search_results" >
        {include file=$rsTemplate}
    </div>
    <div id="showMoreDiv"  onclick="SUGAR.FTS.loadMore();" style="{$showMoreDivStyle}">LOAD MORE</div>
</td>
    </tr>
</table>


{if (!$smarty.get.ajax)}

<script>

    var enabled_modules = {$enabled_modules};
    var disabled_modules = {$disabled_modules};
    var lblEnabled = '{sugar_translate label="LBL_ACTIVE_MODULES" module="Administration"}';
    var lblDisabled = '{sugar_translate label="LBL_DISABLED_MODULES" module="Administration"}';
    {literal}
    $('.ftsModuleFilter').bind('click', function(e) {
        SUGAR.FTS.search();
        var textLabel = this.id + '_label';
        if(this.checked)
        {
            $('#'+textLabel).removeClass('unchecked');
        }
        else
        {
            $('#'+textLabel).addClass('unchecked');
        }
    });

    $("#ftsSearchField").keypress(function(event) {
        if(event.keyCode == 13)
            SUGAR.FTS.search();
    });

    SUGAR.FTS = {

        currentOffset: 0,
        limit: 0,
        totalHits: 0,
        getSelectedModules: function()
        {
            var results = [];
            $('#moduleListTD').find('.ftsModuleFilter:checked').each(function(i){
                results.push($(this).attr('id'));
            });
            return results;
        },
        search: function(append)
        {
            //For new searches reset the offset
            if(typeof(append) == 'undefined' || !append)
            {
                SUGAR.FTS.currentOffset = 0;
            }

            $('#sugar_full_search_results').showLoading();
            //TODO: Check if all modules are selected, then don't send anything down.
            var m = this.getSelectedModules();
            var q = $("#ftsSearchField").val();

            $.ajax({
                type: "POST",
                url: "index.php",
                dataType: 'json',
                data: {'action':'spot', 'ajax': true,'full' : true, 'module':'Home', 'to_pdf' : '1',  'q': q, 'm' : m, 'rs_only': true, 'offset': SUGAR.FTS.currentOffset},
                success: function(o)
                {
                    if(typeof(append) != 'undefined' && append)
                    {
                        SUGAR.FTS.totalHits = o.totalHits;
                        $("#sugar_full_search_results").append(o.results);

                    }
                    else
                    {
                        $("#sugar_full_search_results").html(o.results);
                    }
                    $("#totalTime").html(o.totalTime);
                    $("#totalCount").html(o.totalHits);
                    $('#sugar_full_search_results').hideLoading();
                    SUGAR.FTS.toogleShowMore();

                },
                failure: function(o)
                {
                    $('#sugar_full_search_results').hideLoading();
                }
            });
        },
        toogleShowMore : function()
        {
            if( SUGAR.FTS.currentOffset + SUGAR.FTS.limit >= SUGAR.FTS.totalHits)
            {
               $('#showMoreDiv').hide();
            }
            else
            {
               $('#showMoreDiv').show();
            }
        },
        toggleAdvancedOptions: function()
        {
            if (document.getElementById('inlineGlobalSearch').style.display == 'none')
            {
                SUGAR.FTS.globalSearchEnabledTable.render();
                SUGAR.FTS.globalSearchDisabledTable.render();
                document.getElementById('inlineGlobalSearch').style.display = '';
                document.getElementById('basic_search_img_span').style.display = '';
                document.getElementById('advanced_search_img_span').style.display = 'none';
            }
            else
            {
                document.getElementById('inlineGlobalSearch').style.display = 'none';
                document.getElementById('basic_search_img_span').style.display = 'none';
                document.getElementById('advanced_search_img_span').style.display = '';
            }
        },
        globalSearchEnabledTable : new YAHOO.SUGAR.DragDropTable(
                "enabled_div",
                [{key:"label",  label: lblEnabled, width: 200, sortable: false},
                 {key:"module", label: lblEnabled, hidden:true}],
                new YAHOO.util.LocalDataSource(enabled_modules, {
                    responseSchema: {fields : [{key : "module"}, {key : "label"}]}
                }),
                {height: "200px"}
        ),
        globalSearchDisabledTable : new YAHOO.SUGAR.DragDropTable(
                "disabled_div",
                [{key:"label",  label: lblDisabled, width: 200, sortable: false},
                 {key:"module", label: lblDisabled, hidden:true}],
                new YAHOO.util.LocalDataSource(disabled_modules, {
                    responseSchema: {fields : [{key : "module"}, {key : "label"}]}
                }),
                {height: "200px"}
        ),
        saveModuleFilterSettings : function()
        {
            var enabledTable = SUGAR.FTS.globalSearchDisabledTable;
            var modules = "";
            for(var i=0; i < enabledTable.getRecordSet().getLength(); i++){
                var data = enabledTable.getRecord(i).getData();
                if (data.module && data.module != '')
                    modules += "," + data.module;
            }
            modules = modules == "" ? modules : modules.substr(1);
            document.getElementById('disabled_modules').value = modules;
        },
        loadMore: function()
        {
            SUGAR.FTS.currentOffset += SUGAR.FTS.limit;
            SUGAR.FTS.search(true);
        }
    }

    //Setup autocomplete
    var data = encodeURIComponent(YAHOO.lang.JSON.stringify({'method':'fts_query','conditions':[]}));
    var autoCom = $( "#ftsSearchField" ).autocomplete({
        source: 'index.php?to_pdf=true&module=Home&action=quicksearchQuery&full=true&rs_only=true&data='+data,
        select: function(event, ui) {},
        minLength: 3,
        search: function(event,ui){
            $('#sugar_full_search_results').showLoading();
        }
        }).data( "autocomplete" )._response = function(content)
        {
            var el = $("#sugar_full_search_results");

            if(typeof(content.results) != 'undefined'){
                el.html( content.results);
                SUGAR.FTS.totalHits = content.totalHits;
                $("#totalCount").html(SUGAR.FTS.totalHits);
                $("#totalTime").html(content.totalTime);
            }
            this.pending--;
            SUGAR.FTS.toogleShowMore();
            $('#sugar_full_search_results').hideLoading();
        };

    //Overload the search function so we can pass additional arguments into the source call.
    (function($) {
        $.extend(true, $["ui"]["autocomplete"].prototype, {
            _search: function(value) {
                var self = this;
                self.pending++;
                var m = SUGAR.FTS.getSelectedModules();
                var data = { term: value, m: m };
                SUGAR.FTS.currentOffset = 0;
                self.source(data, self.response );
            }
        });
    })(jQuery);
    //Setup enable table
    SUGAR.FTS.globalSearchEnabledTable.disableEmptyRows = true;
    SUGAR.FTS.globalSearchDisabledTable.disableEmptyRows = true;
    SUGAR.FTS.globalSearchEnabledTable.addRow({module: "", label: ""});
    SUGAR.FTS.globalSearchDisabledTable.addRow({module: "", label: ""});
    SUGAR.FTS.globalSearchEnabledTable.render();
    SUGAR.FTS.globalSearchDisabledTable.render();
    {/literal}
    SUGAR.FTS.offset = {$offset};
    SUGAR.FTS.limit = {$limit};
    SUGAR.FTS.totalHits = {$totalHits};
</script>

{/if}

