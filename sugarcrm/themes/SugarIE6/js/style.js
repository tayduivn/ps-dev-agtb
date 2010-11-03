/**
 * style.js javascript file
 *
 * LICENSE: The contents of this file are subject to the SugarCRM Professional
 * End User License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You
 * may not use this file except in compliance with the License.  Under the
 * terms of the license, You shall not, among other things: 1) sublicense,
 * resell, rent, lease, redistribute, assign or otherwise transfer Your
 * rights to the Software, and 2) use the Software for timesharing or service
 * bureau purposes such as hosting the Software for commercial gain and/or for
 * the benefit of a third party.  Use of the Software may be subject to
 * applicable fees and any use of the Software without first paying applicable
 * fees is strictly prohibited.  You do not have the right to remove SugarCRM
 * copyrights from the source code or user interface.
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
 * by SugarCRM are Copyright (C) 2005 SugarCRM, Inc.; All Rights Reserved.
 */
 
// $Id: style.js 23344 2007-06-05 20:32:59Z eddy $

YAHOO.util.Event.onDOMReady(function() 
{
    // This enables the iframe workaround for IE6 for the dropdown menus
    try {
        YAHOO.widget.AutoComplete.prototype.useIFrame = true;
    } 
    catch (e) {
    }
    // Bug 32389 - Fix styling of UpgradeWizard 'Upgrade Steps' box; can't be done thru CSS
    var nodes = YAHOO.util.Selector.query('.detail tr td[scope="row"]');
    for (var i = 0; i < nodes.length; i++) {
        nodes[i].style.backgroundColor = '#f6f6f6';
    }
    var nodes = YAHOO.util.Selector.query('.small tr td[scope="row"] table tr td, .small tr td[scope="row"] table tr th');
    for (var i = 0; i < nodes.length; i++) {
        nodes[i].style.backgroundColor = 'transparent';
    }
});

/**
 * shows mouseover popup for moduleTabExtraMenu and globalLinks
 */
YAHOO.util.Event.onContentReady('moduleTabExtraMenu', function(){
    document.getElementById('moduleTabExtraMenu').onmouseover = function(){
        handle = YAHOO.util.Selector.query('a','moduleTabExtraMenu',true);
        handle.id = 'moduleTabExtraMoreHandle';
        menu = YAHOO.util.Selector.query('ul.cssmenu','moduleTabExtraMenu',true);
        menu.id = 'moduleTabExtraMoreMenu';
        tbButtonMouseOver('moduleTabExtraMoreHandle',95,15,0);
    }
});
YAHOO.util.Event.onContentReady('globalLinks', function(){
    items = document.getElementById('globalLinks').getElementsByTagName('li');
    for (var i = 0; i < items.length; i++) {
        if ( !YAHOO.util.Selector.query('ul.cssmenu',items[i],true) )
            continue;
        items[i].onmouseover = function(){
            idName = 'globalLinks'+Math.round(Math.random()*10000).toString()
            handle = YAHOO.util.Selector.query('a',this,true);
            if ( handle.id == '' )
                handle.id = idName+'MoreHandle';
            menu = YAHOO.util.Selector.query('ul.cssmenu',this,true);
            if ( menu.id == '' )
                menu.id = idName+'MoreMenu';
            tbButtonMouseOver(handle.id,25,'',-6);
        }
    }
});
YAHOO.util.Event.onContentReady('subModuleList', function(){
    items = document.getElementById('subModuleList').getElementsByTagName('li');
    for (var i = 0; i < items.length; i++) {
        if ( items[i].className != 'subTabMore' )
            continue;
        items[i].onmouseover = function(){
            idName = 'subModuleList'+Math.round(Math.random()*10000).toString()
            handle = YAHOO.util.Selector.query('a',this,true);
            if ( handle.id == '' )
                handle.id = idName+'MoreHandle';
            menu = YAHOO.util.Selector.query('ul.cssmenu',this,true);
            if ( menu.id == '' )
                menu.id = idName+'MoreMenu';
            tbButtonMouseOver(handle.id,20,-200,0);
        }
    }
});
