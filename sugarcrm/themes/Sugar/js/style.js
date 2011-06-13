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
 *
 * $Id: style.js 23344 2007-06-05 20:32:59Z eddy $
 */

/**
 * Handles the global links slide
 */
YAHOO.util.Event.onContentReady("globalLinksModule", function() 
{
    if ( !Get_Cookie('globalLinksOpen') ) {
        Set_Cookie('globalLinksOpen','true',30,'/','','');
    }
    if ( Get_Cookie('globalLinksOpen') && Get_Cookie('globalLinksOpen') == 'true' ) {
        document.getElementById('globalLinks').style.width = "auto";
    }
    YUI({combine: true, timeout: 10000, base:"include/javascript/yui3/build/", comboBase:"index.php?entryPoint=getYUIComboFile&"}).use("node", "anim", function(Y) {
        var module = Y.one('#globalLinksModule');
    
        if ( Get_Cookie('globalLinksOpen') && Get_Cookie('globalLinksOpen') == 'true' ) {
            var content = module.one('#globalLinks').plug(Y.Plugin.NodeFX, {
            from: { width: 0
                     
            },
            to: {
                width: 
                function(node) { // dynamic in case of change
                        return node.get('scrollWidth'); 
                    }
            },
            easing: Y.Easing.easeOut,
            duration: 0.5
        });
            module.toggleClass('yui-closed');
            
        } else {
        var content = module.one('#globalLinks').plug(Y.Plugin.NodeFX, {
            from: { width: 
                    function(node) { // dynamic in case of change
                        return node.get('scrollWidth'); 
                    } 
            },
            to: {
                width: 0
            },
            easing: Y.Easing.backIn,
            duration: 0.5
        });
        }
        
        var onClick = function(e) {
            module.toggleClass('yui-closed');
            content.fx.set('reverse', !content.fx.get('reverse')); // toggle reverse 
            content.fx.run();
            if ( document.getElementById('globalLinksModule').className == 'yui-closed' )
                Set_Cookie('globalLinksOpen','true',30,'/','','');
            else
                Set_Cookie('globalLinksOpen','false',30,'/','','');
        };
    
        // use dynamic control for dynamic behavior
        var control = Y.Node.create(
        '<a title="show/hide content" class="yui-toggle"><em>toggle</em></a>'
        );
        
        module.one('#globalLinksCtrl').appendChild(control);
        control.on('click', onClick);
    });
});


/**
 * For the module list menu
 */
YAHOO.util.Event.onContentReady("moduleList", function() 
{
    
    
    function onSubmenuBeforeShow(p_sType, p_sArgs) 
    {
		var oElement,
			oBd,
			oShadow,
			oShadowBody,
			oShadowBodyCenter,
			oVR,
		    oLastViewContainer,
			parentIndex,
			oItem,
			oSubmenu,
			data,
			aItems;

			
			parentIndex = this.parent.index;
			
			
		if (this.parent) {
			
			oElement = this.element;
			oBd = oElement.firstChild;
			oShadow = oElement.lastChild;
			oLastViewContainer = document.getElementById("lastViewedContainer"+oElement.id);
            
            // We need to figure out the module name from the ID. Sometimes it will have the group name in it
            // But sometimes it will just use the module name (in the case of the All group which don't have the
            // group prefixes due to the automated testing suite.
            var moduleName = oElement.id;
            var groupName = oElement.parentNode.parentNode.parentNode.id.replace('themeTabGroup_','');
            moduleName = moduleName.replace(groupName+'_','');
            
			var handleSuccess = function(o){
				if(o.responseText !== undefined){			
				data = YAHOO.lang.JSON.parse(o.responseText);
				aItems = oMenuBar.getItems();
				oItem = aItems[parentIndex];
				oSubmenu = oItem.cfg.getProperty("submenu");
				oSubmenu.removeItem(1,1);
				oSubmenu.addItems(data,1);
				
				//update shadow height to accomodate new items

				oVR = oShadow.previousSibling;
				oVR.style.height = (oShadow.offsetHeight - 35)+"px";

				
				
				}
			}

			var handleFailure = function(o){
				if(o.responseText !== undefined){
					oLastViewContainer.innerHTML = "Failed to load menu";
				}
			}

			var callback =
			{
			  success:handleSuccess,
			  failure:handleFailure,
			  argument: { foo:"foo", bar:"bar" }
			};

			var sUrl = "index.php?module="+moduleName+"&action=modulelistmenu";
			
			if(oLastViewContainer && oLastViewContainer.lastChild.firstChild.innerHTML == "&nbsp;") {
				var request = YAHOO.util.Connect.asyncRequest('GET', sUrl, callback);
			}
			

		}

	}
	

	function onSubmenuShow(p_sType, p_sArgs) {

	var oElement,
		oShadow,
		oShadowBody,
		oShadowBodyCenter,
		oBd,
		oVR;

	if (this.parent) {
		
		oElement = this.element;
		var newLeft = oElement.offsetLeft + offsetPadding;
		oElement.style.left = newLeft + "px";	
		oBd = oElement.firstChild;
		oShadow = oElement.lastChild;
		oElement.style.top = oElement.offsetTop + 1 + "px";
		if(oElement.id.substr(0,4) != "More" && oElement.id.substring(0,8) != "TabGroup") {
			if(oShadow.previousSibling.className != "vr") {
			
			oVR = document.createElement("div");
			oVR.setAttribute("class", "vr");
			oVR.setAttribute("className", "vr");
			oElement.insertBefore(oVR,oShadow);
			
			oVR.style.height = (oBd.offsetHeight - 15)+"px";
			oVR.style.top = (oBd.offsetTop+8) +"px";
			oVR.style.left = ((oBd.offsetWidth/2)-10) +"px";

			}
		}
		
		}
			
	}

    var nodes = YAHOO.util.Selector.query('#moduleList>div');
    allMenuBars = new Object();

    for ( var i = 0 ; i < nodes.length ; i++ ) {
	    var currMenuBar = new YAHOO.widget.MenuBar(nodes[i].id, { 
		    autosubmenudisplay: true, 
            visible: false,
		    hidedelay: 750, 
		    lazyload: true });
       
        
	    /*
	      Subscribe to the "beforeShow" and "show" events for 
	      each submenu of the MenuBar instance.
	    */
	    
	    currMenuBar.subscribe("beforeShow", onSubmenuBeforeShow);
	    currMenuBar.subscribe("show", onSubmenuShow);
        
	    /*
	      Call the "render" method with no arguments since the 
	      markup for this MenuBar already exists in the page.
	    */
        
	    currMenuBar.render();
        allMenuBars[nodes[i].id.substr(nodes[i].id.indexOf('_')+1)] = currMenuBar;
        
        
        
        if (typeof YAHOO.util.Dom.getChildren(nodes[i]) == 'object' && YAHOO.util.Dom.getChildren(nodes[i]).shift().style.display != 'none') 
        {
            // This is the currently displayed menu bar
            oMenuBar = currMenuBar;
        }
    }

	
	// Remove the href attribute if we are on an touch device ( like an iPad )
	if ( SUGAR.util.isTouchScreen() ) {
	    var nodes = YAHOO.util.Selector.query('#moduleList a.yuimenubaritemlabel-hassubmenu');
	    YAHOO.util.Dom.batch(nodes, function(el,o) {
	        el.href = '#';
	    });
	}

});

/**
 * For the module list menu scrolling functionality
 */
YAHOO.util.Event.onContentReady("tabListContainer", function() 
{
    YUI({combine: true, timeout: 10000, base:"include/javascript/yui3/build/", comboBase:"index.php?entryPoint=getYUIComboFile&"}).use("anim", function(Y) 
    {
        var content = Y.one('#content');
        //BEGIN SUGARCRM flav!=sales ONLY
        var addPage = Y.one('#add_page');
        //END SUGARCRM flav!=sales ONLY
        var tabListContainer = Y.one('#tabListContainer');
        var tabList = Y.one('#tabList');
        var dashletCtrlsElem = Y.one('#dashletCtrls');
        var contentWidth = content.get('offsetWidth');
        var dashletCtrlsWidth = dashletCtrlsElem.get('offsetWidth')+10;
        //BEGIN SUGARCRM flav!=sales ONLY
        var addPageWidth = addPage.get('offsetWidth')+2;
        //END SUGARCRM flav!=sales ONLY
        var tabListContainerWidth = tabListContainer.get('offsetWidth');
        var tabListWidthElem = tabList.get('offsetWidth');
        //BEGIN SUGARCRM flav!=sales ONLY
        var maxWidth = (contentWidth-3)-(dashletCtrlsWidth+addPageWidth+2);
        //END SUGARCRM flav!=sales ONLY
        
        var tabListChildren = tabList.get('children');
        
        var tabListWidth = 0;
        for(i=0;i<tabListChildren.size();i++) {
            if(Y.UA.ie == 7) {
				tabListWidth += tabListChildren.item(i).get('offsetWidth')+2;
			} else {
				tabListWidth += tabListChildren.item(i).get('offsetWidth');
			}
        }
        
        //BEGIN SUGARCRM flav!=sales ONLY
        if(tabListWidth > maxWidth) {
            tabListContainer.setStyle('width',maxWidth+"px");
            tabList.setStyle('width',tabListWidth+"px");
            tabListContainer.addClass('active');
        }
        //END SUGARCRM flav!=sales ONLY
        
    
        var node = Y.one('#tabListContainer .yui-bd');
        var anim = new Y.Anim({
            node: node,
            to: {
                scroll: function(node) {
                    return [node.get('scrollLeft') + node.get('offsetWidth'),0]
                }
            },
            easing: Y.Easing.easeOut
        });
    
        var onClick = function(e) {
    
            var y = node.get('offsetWidth');
            if (e.currentTarget.hasClass('yui-scrollup')) {
                y = 0 - y;
            }
    
            anim.set('to', { scroll: [y + node.get('scrollLeft'),0] });
            anim.run();
        };
    
        Y.all('#tabListContainer .yui-hd a').on('click', onClick);
    });
});

function sugar_theme_gm_switch( groupName ) {
    document.getElementById('themeTabGroup_'+sugar_theme_gm_current).style.display='none';
    sugar_theme_gm_current = groupName;
    YAHOO.util.Connect.asyncRequest('POST','index.php?module=Users&action=ChangeGroupTab&to_pdf=true',false,'newGroup='+groupName);
    document.getElementById('themeTabGroup_'+groupName).style.display='block';
    
    oMenuBar = allMenuBars[groupName];
}

offsetPadding = 0;

function resizeHeader() {
	var e = document.getElementById("contentTable");
	document.getElementById("moduleList").style.width = e.offsetWidth + "px";
	document.getElementById("header").style.width = e.offsetWidth + 20 + "px";
	document.getElementById("dcmenu").style.width = e.offsetWidth + 20 + "px";

}