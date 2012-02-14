{if count($resultSet) > 0}
    {foreach from=$resultSet item=result name=searchresult}
    <section class="{if $smarty.foreach.searchresult.index  is even}even{else}odd{/if}">
        <div class="resultTitle">
        
        {$result->getModuleName()|upper}
 		</div>
 		{capture assign=url}index.php?module={$result->getModule()}&record={$result->getId()}&action=DetailView{/capture}
            <ul class='fts_spot_ul' >
                <li >
                    <span class="details">
                        <a href="{sugar_ajax_url url=$url}">
                            {foreach from=$result->getHighlightedHitText(80, 1, '<span class="highlight">', '</span>') key=k item=v}
                                {$k}: {$v}
                                <br>
                            {/foreach}
                        </a>
                    </span>
                     <span class="spot_fts_summary">{$result->getSummaryText()}</span>
                </li>
            </ul>
        <div class="clear"></div>
    </section>
    {/foreach}
    
    <p class="fullResults"><a href="index.php?module=Home&action=spot&full=true&q={$queryEncoded}">{$APP.LBL_EMAIL_SHOW_READ}</a></p>
{elseif !isset($resultSet) }
    <section class="resultNull">
        <h1>{$APP.LBL_SEARCH_UNAVAILABLE}</h1>
   	</section>
{else}
	<section class="resultNull">
        <h1>{$APP.LBL_EMAIL_SEARCH_NO_RESULTS}</h1>
   	</section>
{/if}
