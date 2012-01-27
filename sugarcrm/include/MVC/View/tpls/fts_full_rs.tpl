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