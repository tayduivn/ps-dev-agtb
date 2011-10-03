{literal}
<style type="text/css">
.QuickView {
height:12px;
cursor:pointer;
}
.SpanQuickView {
visibility:hidden;
}
.gs_link {
padding-left:15px;
}
.gs_div {
white-space: nowrap;
}
</style>
{/literal}
<div id='SpotResults'>
{if !empty($displayResults)}
{foreach from=$displayResults key=module item=data}
<div>
    {if isset($appListStrings.moduleList[$modulepair])}
        {$appListStrings.moduleList[$module]}
    {else}
        {$module}
    {/if}
    {if !empty($displayMoreForModule[$module])}
    {assign var="more" value=$displayMoreForModule[$module]}
    <small class='more' onclick="DCMenu.spotZoom('{$more.query}', '{$module}', '{$more.offset}');">({$more.countRemaining} {$appStrings.LBL_SEARCH_MORE})</small>
    {/if}
</div>
<ul>
{foreach from=$data key=id item=name}
<li><div onmouseover="DCMenu.showQuickViewIcon('{$id}')" onmouseout="DCMenu.hideQuickViewIcon('{$id}')" class="gs_div"><span id="gs_div_{$id}" class="SpanQuickView"><img id="gs_img_{$id}" class="QuickView" src="themes/default/images/Search.gif" alt="quick_view_{$id}" onclick="DCMenu.showQuickView('{$module}', '{$id}');"></span><a href="index.php?module={$module}&action=DetailView&record={$id}" class="gs_link">{$name}</a></div></li>
{/foreach}
</ul>
{/foreach}
{else}
{$appStrings.LBL_EMAIL_SEARCH_NO_RESULTS}
{/if}
<p>
<button onclick="document.location.href='index.php?module=Home&action=UnifiedSearch&search_form=false&advanced=false&query_string={$queryEncoded}'">{$appStrings.LBL_EMAIL_SHOW_READ}</button>
</div>