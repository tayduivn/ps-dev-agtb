
{if (!$smarty.get.ajax)}
<br>
<input type="text" size="50" placeholder="{$APP.LBL_SEARCH}" id="ftsSearchField">
<div id="ftsAutoCompleteResult"></div>
<br><br>
{/if}


<table width="100%">
<tr><td width="10%"><b>Module</b></td><td width="90%"></td></tr>
<tr valign="top">
    <td id="moduleListTD">
        {foreach from=$filterModules item=moduleName key=module}
            <input type="checkbox" checked="checked" id="{$module}" name="module_filter" class="ftsModuleFilter">{$moduleName}<br>
        {/foreach}
    </td>
<td>
<div id="sugar_full_search_results">
{if !empty($resultSet)}
    {foreach from=$resultSet item=result name=searchresult}
    <section class="{if $smarty.foreach.searchresult.index  is even}even{else}odd{/if}">
        <div class="resultTitle">
        
        {$result->getModuleName()}
 		</div>
 		{capture assign=url}index.php?module={$result->getModule()}&record={$result->getId()}&action=DetailView{/capture}
            <ul>
            	<li><a href="{sugar_ajax_url url=$url}"> {$result->getSummaryText()}</a>
                <br>
                <span class="details">
                    {foreach from=$result->getHighlightedHitText(100, 2, '<span class="highlight">', '</span>') key=k item=v}
                        {$k}: {$v}
                        <br>
                    {/foreach}
                </span>
            </ul>
        <div class="clear"></div>
    </section>
    {/foreach}
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
            var m = this.getSelectedModules();
            var q = $("#ftsSearchField").val();
            console.log(q);

            $.ajax({
            type: "POST",
            url: "index.php",
            data: {'action':'spot', 'ajax': true,'full' : true, 'module':'Home', 'to_pdf' : '1',  'q': q, 'm' : m},
            success: function(o)
            {
            $("#sugar_full_search_results").html( o );

            console.log(o);
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

