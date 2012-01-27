<script type="text/javascript" src="cache/include/javascript/sugar_grp_yui_widgets.js"></script>
<link rel="stylesheet" type="text/css" href="{sugar_getjspath file='modules/Connectors/tpls/tabs.css'}"/>

{if (!$smarty.get.ajax)}
    <br>
    <input type="text" size="50" placeholder="{$APP.LBL_SEARCH}" id="ftsSearchField" value="{$smarty.request.q}">

    <a class='tabFormAdvLink' href='javascript:SUGAR.FTS.toggleAdvancedOptions();'>
        {sugar_getimage alt=$alt_show_hide name="advanced_search" ext=".gif" other_attributes='border="0" id="up_down_img" '}
    </a>
    <div id="ftsAutoCompleteResult"></div>
    <br><br>

    <div id='inlineGlobalSearch' class='add_table' style="display:none;">
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
        </table>
    </div>

{/if}


<table width="50%">
<tr><td width="15%"><b>Module</b></td><td width="90%"></td></tr>
<tr valign="top">
    <td id="moduleListTD">
        {foreach from=$filterModules item=entry key=module}
            <input type="checkbox" checked="checked" id="{$entry.module}" name="module_filter" class="ftsModuleFilter">{$entry.label}<br>
        {/foreach}
    </td>
<td>
<div id="sugar_full_search_results" >
{if count($resultSet) > 0}
    {include file=$rsTemplate}
{else}
	<section class="resultNull">
    {$APP.LBL_EMAIL_SEARCH_NO_RESULTS}
   	</section>
{/if}
    <br>
</div>
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
    $('.ftsModuleFilter').bind('click', function() {
        SUGAR.FTS.search();
    });

    $("#ftsSearchField").keypress(function(event) {
        if(event.keyCode == 13)
            SUGAR.FTS.search();
    });

    SUGAR.FTS = {

        getSelectedModules: function()
        {
            var results = [];
            $('#moduleListTD').find('.ftsModuleFilter:checked').each(function(i){
                results.push($(this).attr('id'));
            });
            return results;
        },
        search: function()
        {
            $('#sugar_full_search_results').showLoading();
            //TODO: Check if all modules are selected, then don't send anything down.
            var m = this.getSelectedModules();
            var q = $("#ftsSearchField").val();

            $.ajax({
                type: "POST",
                url: "index.php",
                data: {'action':'spot', 'ajax': true,'full' : true, 'module':'Home', 'to_pdf' : '1',  'q': q, 'm' : m, 'rs_only': true},
                success: function(o)
                {
                    $("#sugar_full_search_results").html( o );
                    $('#sugar_full_search_results').hideLoading();
                },
                failure: function(o)
                {
                    $('#sugar_full_search_results').hideLoading();
                }
            });
        },
        toggleAdvancedOptions: function()
        {
            if (document.getElementById('inlineGlobalSearch').style.display == 'none')
            {
                SUGAR.FTS.globalSearchEnabledTable.render();
                SUGAR.FTS.globalSearchDisabledTable.render();
                document.getElementById('inlineGlobalSearch').style.display = '';
                document.getElementById('up_down_img').src='index.php?entryPoint=getImage&imageName=basic_search.gif';
                document.getElementById('up_down_img').setAttribute('alt',SUGAR.language.get('app_strings', 'LBL_ALT_HIDE_OPTIONS'));
            }
            else
            {
                document.getElementById('inlineGlobalSearch').style.display = 'none';
                document.getElementById('up_down_img').src='index.php?entryPoint=getImage&imageName=advanced_search.gif';
                document.getElementById('up_down_img').setAttribute('alt',SUGAR.language.get('app_strings', 'LBL_ALT_SHOW_OPTIONS'));
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
            )
    }

    var ds = new YAHOO.util.DataSource("index.php?", {
        responseType: YAHOO.util.XHRDataSource.TYPE_JSON,
        responseSchema: {
            resultsList: 'results'
        },
        connMethodPost: true
        });

        var search = new YAHOO.widget.AutoComplete("ftsSearchField", "ftsAutoCompleteResult", ds, {
        generateRequest : function(sQuery) {
        	                    	var out = SUGAR.util.paramsToUrl({
        	                    		to_pdf: 'true',
        	                            module: 'Home',
        	                            action: 'quicksearchQuery',
                                        data: encodeURIComponent(YAHOO.lang.JSON.stringify({'method':'fts_query','conditions':[]})),
        	                            query: sQuery
        	                    	});
        	                    	return out;
        	                    }
    });

    SUGAR.FTS.globalSearchEnabledTable.disableEmptyRows = true;
    SUGAR.FTS.globalSearchDisabledTable.disableEmptyRows = true;
    SUGAR.FTS.globalSearchEnabledTable.addRow({module: "", label: ""});
    SUGAR.FTS.globalSearchDisabledTable.addRow({module: "", label: ""});
    SUGAR.FTS.globalSearchEnabledTable.render();
    SUGAR.FTS.globalSearchDisabledTable.render();


</script>
{/literal}
{/if}

