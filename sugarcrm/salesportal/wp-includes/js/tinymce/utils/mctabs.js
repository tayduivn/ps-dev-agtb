/**
 * $RCSfile: mctabs.js,v $
 * $Revision: 1.1 $
 * $Date: 2005/08/01 18:36:35 $
 *
 * Moxiecode DHTML Tabs script.
 *
 * @author Moxiecode
 * @copyright Copyright © 2004, Moxiecode Systems AB, All rights reserved.
 */
function MCTabs(){this.settings=new Array();};MCTabs.prototype.init=function(settings){this.settings=settings;};MCTabs.prototype.getParam=function(name,default_value){var value=null;value=(typeof(this.settings[name])=="undefined")?default_value:this.settings[name];if(value=="true"||value=="false")
return(value=="true");return value;};MCTabs.prototype.displayTab=function(tab_id,panel_id){var panelElm=document.getElementById(panel_id);var panelContainerElm=panelElm?panelElm.parentNode:null;var tabElm=document.getElementById(tab_id);var tabContainerElm=tabElm?tabElm.parentNode:null;var selectionClass=this.getParam('selection_class','current');if(tabElm&&tabContainerElm){var nodes=tabContainerElm.childNodes;for(var i=0;i<nodes.length;i++){if(nodes[i].nodeName=="LI")
nodes[i].className='';}
tabElm.className='current';}
if(panelElm&&panelContainerElm){var nodes=panelContainerElm.childNodes;for(var i=0;i<nodes.length;i++){if(nodes[i].nodeName=="DIV")
nodes[i].className='panel';}
panelElm.className='current';}};MCTabs.prototype.getAnchor=function(){var pos,url=document.location.href;if((pos=url.lastIndexOf('#'))!=-1)
return url.substring(pos+1);return"";};var mcTabs=new MCTabs();