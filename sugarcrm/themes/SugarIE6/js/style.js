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
