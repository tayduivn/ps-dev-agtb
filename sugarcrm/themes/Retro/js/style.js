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
 
// $Id: style.js 23344 2007-06-05 20:32:59Z eddy $

/**
 * Handles loading the sitemap popup
 */
YAHOO.util.Event.onAvailable('sitemapLinkSpan',function()
{
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
                     left = 0;
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
});
