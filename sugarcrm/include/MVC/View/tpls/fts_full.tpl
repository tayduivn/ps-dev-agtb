
{if (!$smarty.get.ajax)}
<br>
<input type="text" size="50" placeholder="{$appStrings.LBL_SEARCH}" id="ftsSearchField">
<div id="ftsAutoCompleteResult"></div>
<br><br>
{/if}

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
                    {/foreach}
                </span>
            </ul>
        <div class="clear"></div>
    </section>
    {/foreach}
{else}
	<section class="resultNull">
    {$appStrings.LBL_EMAIL_SEARCH_NO_RESULTS}
   	</section>
{/if}
    <br>
</div>

{if (!$smarty.get.ajax)}
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
{/if}

