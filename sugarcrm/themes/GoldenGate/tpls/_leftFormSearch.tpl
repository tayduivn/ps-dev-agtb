{*
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-enterprise-eula.html
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2006 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
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
            {sugar_getimage name="MoreDetail" alt=$app_strings.LBL_MOREDETAIL ext=".png" alt=$APP.LBL_ADVANCED_SEARCH other_attributes='border="0" '}&nbsp;
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
