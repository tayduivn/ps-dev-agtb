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
 * Handles changing the sub menu items when using grouptabs
 */
YAHOO.util.Event.onAvailable('subModuleList',IKEADEBUG);
function IKEADEBUG()
{
    var moduleLinks = document.getElementById('moduleList').getElementsByTagName("a");
    moduleLinkMouseOver = function() 
        {
            var matches      = /grouptab_([0-9]+)/i.exec(this.id);
            var tabNum       = matches[1];
            var moduleGroups = document.getElementById('subModuleList').getElementsByTagName("span"); 
            for (var i = 0; i < moduleGroups.length; i++) { 
                if ( i == tabNum ) {
                    moduleGroups[i].className = 'selected';
                }
                else {
                    moduleGroups[i].className = '';
                }
            }
            
            var groupList = document.getElementById('moduleList').getElementsByTagName("li");
			var currentGroupItem = tabNum;
            for (var i = 0; i < groupList.length; i++) {
                var aElem = groupList[i].getElementsByTagName("a")[0];
                if ( aElem == null ) {
                    // This is the blank <li> tag at the start of some themes, skip it
                    continue;
                }
                // notCurrentTabLeft, notCurrentTabRight, notCurrentTab
                var classStarter = 'notC';
                if ( aElem.id == "grouptab_"+tabNum ) {
                    // currentTabLeft, currentTabRight, currentTab
                    classStarter = 'c';
					currentGroupItem = i;
                }
                var spanTags = groupList[i].getElementsByTagName("span");
                for (var ii = 0 ; ii < spanTags.length; ii++ ) {
                    if ( spanTags[ii].className == null ) { continue; }
                    var oldClass = spanTags[ii].className.match(/urrentTab.*/);
                    spanTags[ii].className = classStarter + oldClass;
                }
            }
            ////////////////////////////////////////////////////////////////////////////////////////
			////update submenu position
			//get sub menu dom node
			var menuHandle = moduleGroups[tabNum];
			
			//get group tab dom node
			var parentMenu = groupList[currentGroupItem];

			if(menuHandle && parentMenu){
				updateSubmenuPosition(menuHandle , parentMenu);
			}
			////////////////////////////////////////////////////////////////////////////////////////
        };
    for (var i = 0; i < moduleLinks.length; i++) {
        moduleLinks[i].onmouseover = moduleLinkMouseOver;
    }
};

function updateSubmenuPosition(menuHandle , parentMenu){	
	var left='';
	if (left == "") {
		p = parentMenu;
		var left = 0;
		while(p&&p.tagName.toUpperCase()!='BODY'){
			left+=p.offsetLeft;
			p=p.offsetParent;
		}
	}

	//get browser width
	var bw = checkBrowserWidth();
	
	//If the mouse over on 'MoreMenu' group tab, stop the following function
	if(!parentMenu){
		return;
	}
	//Calculate left position of the middle of current group tab .
	var groupTabLeft = left + (parentMenu.offsetWidth / 2);
	var subTabHalfLength = 0;
	var children = menuHandle.getElementsByTagName('li');
	for(var i = 0; i< children.length; i++){
		//offsetWidth = width + padding + border
		if(children[i].className == 'subTabMore' || children[i].parentNode.className == 'cssmenu'){
			continue;
		}
		subTabHalfLength += parseInt(children[i].offsetWidth);
	}
	
	if(subTabHalfLength != 0){
		subTabHalfLength = subTabHalfLength / 2;
	}
	
	var totalLengthInTheory = subTabHalfLength + groupTabLeft;
	if(subTabHalfLength>0 && groupTabLeft >0){
		if(subTabHalfLength >= groupTabLeft){
			left = 1;
		}else{
			left = groupTabLeft - subTabHalfLength;
		}
	}
	
	//If the sub menu length > browser length
	if(totalLengthInTheory > bw){
		var differ = totalLengthInTheory - bw;
		left = groupTabLeft - subTabHalfLength - differ - 2;
	}
	
	if (left >=0){
		menuHandle.style.marginLeft = left+'px';
	}
}

YAHOO.util.Event.onDOMReady(function()
{
	if ( document.getElementById('subModuleList') ) {
	    ////////////////////////////////////////////////////////////////////////////////////////
        ////update current submenu position
        var parentMenu = false;
        var moduleListDom = document.getElementById('moduleList');
        if(moduleListDom !=null){
            var parentTabLis = moduleListDom.getElementsByTagName("li");
            var tabNum = 0;
            for(var ii = 0; ii < parentTabLis.length; ii++){
                var spans = parentTabLis[ii].getElementsByTagName("span");
                for(var jj =0; jj < spans.length; jj++){
                    if(spans[jj].className.match(/currentTab.*/)){
                        tabNum = ii;
                    }
                }
            }
            var parentMenu = parentTabLis[tabNum];
        }
        var moduleGroups = document.getElementById('subModuleList').getElementsByTagName("span"); 
        for(var i = 0; i < moduleGroups.length; i++){
            if(moduleGroups[i].className.match(/selected/)){
                tabNum = i;
            }
        }
        var menuHandle = moduleGroups[tabNum];
	
        if(menuHandle && parentMenu){
            updateSubmenuPosition(menuHandle , parentMenu);
        }
    }
	////////////////////////////////////////////////////////////////////////////////////////
});
