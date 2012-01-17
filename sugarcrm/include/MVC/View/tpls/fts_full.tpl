
<br>
<input type="text" size="100" id="ftsSearchField">
<div id="ftsAutoCompleteResult"></div>
<br><br>


{if !empty($resultSet)}
    {foreach from=$resultSet item=result}

        {capture assign=url}index.php?module={$result->getModule()}&record={$result->getId()}&action=DetailView{/capture}
        <a href="{sugar_ajax_url url=$url}">{$result->getModuleName()}:  {$result->getSummaryText()} </a><br>
        <i>{$result->getHighlightedHitText()}</i>
        <br><br>

    {/foreach}
{else}
    {$appStrings.LBL_EMAIL_SEARCH_NO_RESULTS}
{/if}

<br>

{literal}
<script>
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
