/* This is a simple plugin to render action dropdown menus from html.
 * 
 * When it is called, it will render every menu on a page and add necessary callbacks. 
 * Any menu that was previously initialized will be left alone, and any 
 * new items will be added.
 */
(function($){
	$.fn.sugarActionMenu = function() {
		
		//Fix custom code buttons programatically to prevent metadata edits
		$("ul.subnav input[type='submit']").each(function(index, node){
			var jNode = $(node);
			var parent = jNode.parent();
		
			if(parent.is("ul") && parent.hasClass("subnav") && jNode.css("display") != "none"){
				var newItem = $(document.createElement("li"));
				var newItemA = $(document.createElement("a"));
				newItemA.html(jNode.val());
				newItemA.click(function(event){
					jNode.click();
				});
					
				newItem.append(newItemA);
				jNode.before(newItem);
				jNode.css("display", "none");
			}
		});
		
		//look for all subnavs and set them up
		$("ul.clickMenu ul.subnav").each(function(index, node){
			var jNode = $(node);
			var parent = jNode.parent();
			var fancymenu = "";
			var slideUpSpeed = "fast";
			var slideDownSpeed = "fast";
			
			//if the dropdown handle doesn't exist, lets create it and 
			//add it to the dom
			if(parent.find("span").length == 0){
			
				//create dropdown handle
				var dropDownHandle = $(document.createElement("span"));
				if(jNode.hasClass("fancymenu")){
					dropDownHandle.addClass("ab");
				}
			
				dropDownHandle.tipTip({maxWidth: "auto", 
									   edgeOffset: 10, 
				                       content: "More Actions", 
				                       defaultPosition: "top"});
				
				//add click handler to handle
				dropDownHandle.click(function(event){
					//close all other open menus
					$("ul.clickMenu ul.subnav").each(function(subIndex, node){
						$(node).slideUp(slideUpSpeed);	
					});
					if(jNode.hasClass("ddopen")){
						jNode.slideUp(slideUpSpeed);
						jNode.removeClass("ddopen");
					}
					else{
						jNode.slideDown(slideDownSpeed).show();
						jNode.addClass("ddopen");	
					}
				});
			
				//add hover handler to handle
				dropDownHandle.hover(function(){
					dropDownHandle.addClass("subhover");
				}, function(){
					dropDownHandle.removeClass("subhover");
				});
			
				parent.append(dropDownHandle);
			}
			
			//when mouse hovers out of the subnav, slid it back up
			jNode.hover(function(){}, function(){
				jNode.slideUp(slideUpSpeed);
				jNode.removeClass("ddopen");
			});
			
			//bind click event to submeu items to hide the menu on click
			jNode.find("li").each(function(index, subnode){
				$(subnode).bind("click", function(){
					jNode.slideUp(slideUpSpeed);
					jNode.removeClass("ddopen");
				});
			});
		});

}})(jQuery);  	