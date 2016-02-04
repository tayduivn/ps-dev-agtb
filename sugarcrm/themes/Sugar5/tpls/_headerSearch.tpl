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
<div id="search">
    <form name='UnifiedSearch' action='index.php' onsubmit='return SUGAR.unifiedSearchAdvanced.checkUsaAdvanced()'>
        <input type="hidden" name="action" value="UnifiedSearch">
        <input type="hidden" name="module" value="Home">
        <input type="hidden" name="search_form" value="false">
        <input type="hidden" name="advanced" value="false">
        {sugar_getimage name="searchMore" ext=".gif" alt=$APP.LBL_SEARCH other_attributes='border="0" id="unified_search_advanced_img" '}&nbsp;
        <input type="text" name="query_string" id="query_string" size="20" value="{$SEARCH}">&nbsp;
        <input type="submit" class="button" value="{$APP.LBL_SEARCH}">
    </form><br />
    <div id="unified_search_advanced_div"> </div>
</div>
<div id="sitemapLink">
    <span id="sitemapLinkSpan">
        {$APP.LBL_SITEMAP}
        {sugar_getimage name="MoreDetail" alt=$app_strings.LBL_MOREDETAIL ext=".png" other_attributes=''}
    </span>
</div>
<span id='sm_holder'></span>
{/if}
