
{foreach from=$resultSet item=result}

    {capture assign=url}index.php?module={$result->getModule()}&record={$result->getId()}&action=DetailView{/capture}
<a href="{sugar_ajax_url url=$url}">{$result->getModuleName()}:  {$result->getSummaryText()} </a><br>

 <i>{$result->getHighlightedHitText()}</i>



<br>
<br>
{/foreach}