{*
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
*}
{if $AUTHENTICATED}
<div id="search" class="leftList">
    <h3><span>{$APP.LBL_SEARCH}</span></h3>
    <ul>
        <li>
            <form name='UnifiedSearch' onsubmit='return SUGAR.unifiedSearchAdvanced.checkUsaAdvanced()'>
            <input type="hidden" name="action" value="UnifiedSearch">
            <input type="hidden" name="module" value="Home">
            <input type="hidden" name="search_form" value="false">
            <input type="hidden" name="advanced" value="false">
            <input type="text" class="searchField" name="query_string" id="query_string" size="20" value="{$SEARCH}">&nbsp;
            <input type="submit" class="button" value="GO">
            </form>
        </li>
        <li id="unified_search_advanced_div" style="display: none; height: 1px; position: absolute; overflow: hidden; width: 300px; padding-top: 5px; left:-62px;"> </li>
        <li> <a id="unified_search_advanced_img">
            Advanced Search
            {sugar_getimage name="MoreDetail" ext=".png" alt=$APP.LBL_ADVANCED_SEARCH other_attributes='border="0" '}&nbsp;
            </a>
        </li>

        <li id="sitemapLink">
            <span id="sitemapLinkSpan">
            {$APP.LBL_SITEMAP}
            {sugar_getimage name="MoreDetail" alt=$app_strings.LBL_MOREDETAIL ext=".png" other_attributes=''}
            </span>
            <span id='sm_holder'></span>
        </li>
    </ul>
</div>
{literal}
<script type="text/javascript">
<!--
document.getElementById('sitemapLinkSpan').onclick = function()
{
    ajaxStatus.showStatus(SUGAR.language.get('app_strings', 'LBL_LOADING_PAGE'));

    var smMarkup = '';
    var callback = {
         success:function(r) {     
             ajaxStatus.hideStatus();
             document.getElementById('sm_holder').innerHTML = r.responseText;
             with ( document.getElementById('sitemap').style ) {
                 display = "block";
                 position = "absolute";
                 right = 0;
                 top = 80;
             }
             document.getElementById('sitemapClose').onclick = function()
             {
                 document.getElementById('sitemap').style.display = "none";
             }
         } 
    } 
    postData = 'module=Home&action=sitemap&GetSiteMap=now&sugar_body_only=true';    
    YAHOO.util.Connect.asyncRequest('POST', 'index.php', callback, postData);
}
-->
</script>
{/literal}
{/if}
