{literal} 

<style type="text/css">





</style>

{/literal}
{if (!$smarty.get.ajax)}
<br>
<input type="text" size="50" placeholder="{$APP.LBL_SEARCH}" id="ftsSearchField" value="{$smarty.request.q}">
<div id="ftsAutoCompleteResult"></div>
<br><br>
{/if}


<table width="50%">
<tr><td width="15%"><b>Module</b></td><td width="90%"></td></tr>
<tr valign="top">
    <td id="moduleListTD">
        {foreach from=$filterModules item=moduleName key=module}
            <input type="checkbox" checked="checked" id="{$module}" name="module_filter" class="ftsModuleFilter">{$moduleName}<br>
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
{literal}
<script>
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


        }
    }
    function applyModuleFilter()
    {

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

</script>
{/literal}
{/if}

