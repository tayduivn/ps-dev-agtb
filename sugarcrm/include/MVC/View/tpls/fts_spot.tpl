
{if !empty($resultSet)}
    {foreach from=$resultSet item=result}
    <section>
        <div class="resultTitle">
        
        {$result->getModuleName()}
 		</div>
 		{capture assign=url}index.php?module={$result->getModule()}&record={$result->getId()}&action=DetailView{/capture}
            <ul>
                <li><a href="{sugar_ajax_url url=$url}"> {$result->getSummaryText()}</a>
                <br>
                <span class="details">
                    {foreach from=$result->getHighlightedHitText(80, 1, '<span class="highlight">', '</span>') key=k item=v}
                        {$k}: {$v}
                    {/foreach}
                </span>
            </ul>
        <div class="clear"></div>
    </section>
    {/foreach}
    
    <p class="fullResults"><a href="index.php?module=Home&action=spot&full=true&q={$queryEncoded}">{$appStrings.LBL_EMAIL_SHOW_READ}</a></p>
{else}
	<section class="resultNull">
    {$appStrings.LBL_EMAIL_SEARCH_NO_RESULTS}
   	</section>
{/if}
