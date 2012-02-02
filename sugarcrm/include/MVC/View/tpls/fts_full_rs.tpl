{if count($resultSet) > 0}
    {foreach from=$resultSet item=result name=searchresult}
        <section class="{if $smarty.foreach.searchresult.index  is even}even{else}odd{/if}">
            <div class="resultTitle">

            {$result->getModuleName()}
            </div>
            {capture assign=url}index.php?module={$result->getModule()}&record={$result->getId()}&action=DetailView{/capture}
                <ul>
                    <li>
                        <span class="details">
                            {foreach from=$result->getHighlightedHitText(100, 2, '<span class="highlight">', '</span>') key=k item=v}
                                {$k}: {$v}
                                <br>
                            {/foreach}
                        </span>
                        <a href="{sugar_ajax_url url=$url}"> {$result->getSummaryText()}</a>
                    </li>
                </ul>
            <div class="clear"></div>
        </section>
    {/foreach}
{else}
	<section class="resultNull" style="padding: 50px;">
        <h1>{$APP.LBL_EMAIL_SEARCH_NO_RESULTS}</h1>
   	</section>
{/if}
<br>