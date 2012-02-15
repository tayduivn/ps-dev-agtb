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


 //window resize event handers
$(window).resize(function() {
	SUGAR.themes.resizeSearch();
	SUGAR.themes.resizeMenu();
});



$(document).ready(function(){
	SUGAR.themes.resizeSearch();
	SUGAR.themes.resizeMenu();
	//setup for action menus
	SUGAR.themes.actionMenu();
	//setup event handler for search results
	SUGAR.themes.searchResults();
    //setup footer for toggling
    SUGAR.themes.toggleFooter();
    //initialize global tooltips
	SUGAR.themes.globalToolTips();

    $('body').click(function(e) {
        if($(e.target).closest('#dcmenuSearchDiv').length == 0)
        {
            SUGAR.themes.clearSearch();
        }
    });
});

SUGAR.themes = SUGAR.namespace("themes");

SUGAR.append(SUGAR.themes, {
    setRightMenuTab: function(el, params) {

        var extraMenu = "#moduleTabExtraMenu"+sugar_theme_gm_current;
        
		//Check if the menu we want to show is in the more menu
		if($(el+"Overflow").parents().is(extraMenu)) {
			//get the previous sibling of extraMenu
			var $currRight = $(extraMenu).prev();
			//add menu after prev sib

			 $(el+"Overflow").parent().insertAfter($currRight);
			 var newId = el.replace("#","");
			 var currRightId = $currRight.children("a:first-child").attr("id") + "OverflowHidden";
			 $(el+"Overflow").attr("id",newId);
			 $(el).parent().addClass("current");
			 //remove prev sib
			 
			 $(el).parent().prev().remove();
			 $("#"+currRightId).parent().css("display","list-item");
			 
			 
		}
    },
    setCurrentTab: function(params) {
        var el = '#moduleTab_'+ sugar_theme_gm_current + params.module;
        if ($(el) && $(el).parent()) {
            SUGAR.themes.setRightMenuTab(el, params);
            var currActiveTab = "#themeTabGroupMenu_"+sugar_theme_gm_current+" li.current";   
            if ($(currActiveTab)) {
                if ($(currActiveTab) == $(el).parent()) return;
                $(currActiveTab).removeClass("current");
            }
            $(el).parent().addClass("current");
        }
        makeCall = true;
    },
    toggleMenuOverFlow: function(menuName,maction) {
    	
    	var menuName = "#"+menuName;
	    if(maction == "more") {
			$(menuName).addClass("showMore");
			$(menuName).removeClass("showLess");
		} else {
			$(menuName).addClass("showLess");
			$(menuName).removeClass("showMore");
		}
    },
    loadModuleList: function() {
    	$('#moduleList ul.sf-menu').superfish({
			delay:     800,
			autoArrows: false,
			dropShadows: false,
			onBeforeShow: function() {
				if($(this).attr("class") == "megamenu") {
					var extraMenu = "#moduleTabExtraMenu"+sugar_theme_gm_current;
					var moduleName = $(this).prev().attr("id").replace("moduleTab_"+sugar_theme_gm_current,"");
					//Check if the menu we want to show is in the more menu		
					if($(this).parents().is(extraMenu)) {
						var moduleName = moduleName.replace("Overflow","");
						var moduleName = moduleName.replace("Hidden","");
					}
					that = $(this);
					//ajax call for favorites
					if($(this).find("ul.MMFavorites li:last a").html() == "&nbsp;" || makeCall == true) {
						
						$.ajax({
						  url: "index.php?module="+moduleName+"&action=favorites",
						  success: function(json){
						    var lastViewed = $.parseJSON(json);				    
						    $(that).find("ul.MMFavorites").children().not(':eq(0)').remove();
						    $.each(lastViewed, function(k,v) {
						    	if(v.text == "none") {
						    		v.url = "javascript: void(0);";
						    	}
						    	$(that).find("ul.MMFavorites").append("<li><a href=\""+ v.url +"\">"+v.text+"</a></li>");
						    });
						    //update column heights so dividers are even
							$(that).find("ul.MMFavorites li:nth("+lastViewed.length+")").children().ready(function() {
								wrapperHeight = $(that).find("li div.megawrapper").height();
								$(that).find("div.megacolumn-content").css("min-height",wrapperHeight);
							});
						  }
						});
					}					
					//ajax call for last viewed
					if($(this).find("ul.MMLastViewed li:last a").html() == "&nbsp;" || makeCall == true) {
						$.ajax({
						  url: "index.php?module="+moduleName+"&action=modulelistmenu",
						  success: function(json){
						    var lastViewed = $.parseJSON(json);
						    $(that).find("ul.MMLastViewed").children().not(':eq(0)').remove();
						    $.each(lastViewed, function(k,v) {
						    	if(v.text == "none") {
						    		v.url = "javascript: void(0);";
						    	}
						    	$(that).find("ul.MMLastViewed").append("<li><a href=\""+ v.url +"\">"+v.text+"</a></li>");
						    });
						    
						    //update column heights so dividers are even
							$(that).find("ul.MMLastViewed li:nth("+lastViewed.length+")").children().ready(function() {
								wrapperHeight = $(that).find("li div.megawrapper").height();
								$(that).find("div.megacolumn-content").css("min-height",wrapperHeight);
							});
						  }
						});
					}
				makeCall = false;
				}
			},
			onShow: function() {
			}
		});	
    },
    editMenuMode: function() {



    },
    resizeSearch: function() {
    	$('#sugar_spot_search_div').css("width",Math.round($(window).width()*.10) + 54); 
		$('#sugar_spot_search').css("width",Math.round($(window).width()*.10));
    },
    resizeMenu: function () {
	    var maxMenuWidth = Math.round($(window).width()*.45);
		var menuWidth = $('#moduleList').width();
		var menuItemsWidth = $('#moduleTabExtraMenuAll').width();

			$('ul.sf-menu').children("li").each(
				function(index) {
						menuItemsWidth += $(this).width();
					if(menuItemsWidth > maxMenuWidth && $(this).attr("id") != "moduleTabExtraMenu" + sugar_theme_gm_current && !$(this).hasClass("current")) {
			    		//console.log($(this).attr("id"));
			    		$(this).css("display","none");
			    		$("#"+$(this).children("a").attr("id")+"_flex").css("display","list-item");
					}  else if(menuItemsWidth <= maxMenuWidth && $(this).attr("id") != "moduleTabExtraMenu" + sugar_theme_gm_current && !$(this).hasClass("current")) {
						//console.log($(this).attr("id"));
						$(this).css("display","list-item");
						$("#"+$(this).children("a").attr("id")+"_flex").css("display","none");
					}
				}
			);
    },
    globalToolTips: function () {
    	$("#moduleList .home a").tipTip({maxWidth: "auto", edgeOffset: 10});
		$("#arrow").tipTip({maxWidth: "auto", edgeOffset: 10});
		$("#logo").tipTip({maxWidth: "auto", edgeOffset: 10});
		$("#boxnet").tipTip({maxWidth: "auto", edgeOffset: 10});
		$("#linkedin").tipTip({maxWidth: "auto", edgeOffset: 10});
		$("#quickCreateUL span").tipTip({maxWidth: "auto", edgeOffset: 10, content: "Quick Create"});
		$("#dcmenuSugarCube a").tipTip({maxWidth: "auto", edgeOffset: 10});
		$("#sugar_spot_search").tipTip({maxWidth: "auto", edgeOffset: 10});	
    },
    toggleFooter: function () {
	    $("#arrow").click(function(){
	        $(this).toggleClass("up");
	        if ($(this).hasClass('up')) {
	        	$(this).attr("title","Hide");
	        	$("#arrow").tipTip({maxWidth: "auto", edgeOffset: 10});
	            $(this).animate({bottom:'5px'},200);
	        } else {
	        	$(this).attr("title","Show");
	        	$("#arrow").tipTip({maxWidth: "auto", edgeOffset: 10});
	            $(this).animate({bottom:'0'},200);
	        }
	        $("#footer").slideToggle("fast");
	        
	    });	
    },
    searchResults: function () {
    	firstHit = false;
    	$("#sugar_spot_search").keypress(function(event) {
			DCMenu.startSearch(event);
			$('#close_spot_search').css("display","inline-block");
			
			 if(event.charCode == 0 && !firstHit) {
			$('#sugar_spot_search_div').css("left",110);
			$('#sugar_spot_search_div').css("width",344);
			$('#sugar_spot_search').css("width",290);
			firstHit = true;
			 	}
			$('#close_spot_search').click(function() {
				SUGAR.themes.clearSearch();
			});

		});	
    },
    clearSearch: function() {
   		$("div#sugar_spot_search_results").hide();
		$('#close_spot_search').css("display","none");
		$("#sugar_spot_search").val("");
		$("#sugar_spot_search").removeClass("searching");
		$('#sugar_spot_search_div').css("left",0);
		$('#sugar_spot_search_div').css("width",Math.round($(window).width()*.10) + 54);
	  	$('#sugar_spot_search').css("width",Math.round($(window).width()*.10));	
	  	firstHit = false;
   	},
   	actionMenu: function() {
	   	//set up any action style menus
		$("ul.clickMenu").each(function(index, node){
	  		$(node).sugarActionMenu();
	  	});
		
		//Fix show more/show less buttons in top action menus
		$("[class^='moduleMenuOverFlow']").each(function(index,node){
		    var jNode = $(node);
		    jNode.unbind("click");
			jNode.click(function(event){
				event.stopPropagation();
			});
		    
		});	
   	},
   	sugar_theme_gm_switch: function(groupName) {
	   	$('#themeTabGroupMenu_'+sugar_theme_gm_current).css("display","none");
	    sugar_theme_gm_current = groupName;
	    $.ajax({
	    	type: "POST",
	    	url: "index.php?module=Users&action=ChangeGroupTab&to_pdf=true",
	    	data: 'newGroup='+groupName
	    });
	    $('#themeTabGroupMenu_'+groupName).css("display","block");
   	}
    
});

/**
 * For the module list menu
 */

$("#moduleList").ready(function(){
	SUGAR.themes.loadModuleList();
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


