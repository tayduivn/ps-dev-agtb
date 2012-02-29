{if count($resultSet) > 0}
    {foreach from=$resultSet item=result name=searchresult}
        <section class="{if ($smarty.foreach.searchresult.index + $indexOffset) is even}even{else}odd{/if}">
            <div class="resultTitle">
            {$result->getModuleName()|upper}
            </div>
            {capture assign=url}index.php?module={$result->getModule()}&record={$result->getId()}&action=DetailView{/capture}
                <ul>
                    <li>
                        <span class="details">
                            <a href="{sugar_ajax_url url=$url}">
                                {assign var="resultHits" value=$result->getHighlightedHitText(100, 1, '<span class="highlight">', '</span>')}
                                {foreach from=$resultHits key=k item=v}
                                {$k}: {$v}
                                <br>
                            {/foreach}
                            </a>
                        </span>
                        {if empty($resultHits)}
                            <a href="{sugar_ajax_url url=$url}"> <span>{$result->getSummaryText()}</span></a>
                        {else}
                            <span>{$result->getSummaryText()}</span>
                        {/if}

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