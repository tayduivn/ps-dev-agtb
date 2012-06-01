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
function treeinit() {}

if(typeof('console') == 'undefined'){
console = {
	log: function(message){

	}}
}
(function() {
	var sw = YAHOO.SUGAR,
		Event = YAHOO.util.Event,
		Connect = YAHOO.util.Connect,
	    Dom = YAHOO.util.Dom;
	
function createTreePanel(treeData, params) {
	var tree = new YAHOO.widget.TreeView(params.id);
	var root = tree.getRoot();
	addChildNodes(root, treeData);
	
	return tree;
}

function addChildNodes(parentNode, parentData) {
	var nodes = parentData.nodes || parentData.children;
	for (i in nodes) {
		if (typeof(nodes[i]) == 'object') {
			nodes[i].data.href = 'javascript:void(0);';
			var node = new YAHOO.widget.TextNode(nodes[i].data, parentNode)
			node.action = nodes[i].data.action;
			if (typeof(nodes[i].nodes) == 'object') {
				addChildNodes(node, nodes[i]);
			}
		}
	}
}

if (typeof(ModuleBuilder) == 'undefined') {
	ModuleBuilder = {
	    init: function(){
            //Check if we shoudln't be in studio and need to load the normal ajaxUI
            var aRegex = /#.*ajaxUILoc=([^&]*)/.exec(window.location);
            var ajaxLoc = aRegex ? aRegex[1] : false;
            if (ajaxLoc) {
                window.location = "index.php?action=ajaxui#ajaxUILoc=" + ajaxLoc;
                return;
            }
			//Setup the basic ajax request settings
			Connect.extraParams = {
				to_pdf: true
			};
			Connect.url = 'index.php?to_pdf=1&sugar_body_only=1';
			Connect.method = 'POST';
			Connect.timeout = 300000; 
			
			//Setup and read cookie settings
			//Ext.state.Manager.setProvider(new Ext.state.CookieProvider());
			
			if (SUGAR.themes.tempHideLeftCol)
				SUGAR.themes.tempHideLeftCol();
			
			var Ck = YAHOO.util.Cookie;
			
			//Setup the main layout
			var tp = ModuleBuilder.tabPanel = new YAHOO.widget.TabView("mbtabs");
			tp.addTab(new YAHOO.widget.Tab({ 
				label: SUGAR.language.get('ModuleBuilder', 'LBL_SECTION_MAIN'),
				scroll : true,
				content : "<div> </div>",
				id : "center",
				active : true
			}));

			var viewHeight = document.documentElement ? document.documentElement.clientHeight : self.innerHeight;
            var heightOffset = $('#dcmenu').length > 0 ? $('#dcmenu').height() : $('#header').height();
			var mp = ModuleBuilder.mainPanel = new YAHOO.widget.Layout('mblayout', {
				border: false,
				height: viewHeight - heightOffset - 40,
				//autoHeight: true
				//frame: true,
				units: [//ModuleBuilder.tree, ModuleBuilder.tabPanel,
				{
					position: 'center',
					body : 'mbcenter',
					scroll : true
				},{
					position: "left",
					header: "Tree",
					collapse: true,
					width: 230,
					minWidth: 100,
					resize: true,
					scroll : true,
					body : "<div id='mbTree'/>"
				},{
					id: 'help',
					header: SUGAR.language.get('ModuleBuilder', 'LBL_SECTION_HELP'),
					position:'right',
					body: 'mbhelp',
					scroll: true,
					width: 250,
					minWidth: 200,
					resize: true,
					collapse: true
				},{
					header: SUGAR.util.getAndRemove("footerHTML").innerHTML,
					position: 'bottom',
					id: 'mbfooter',
					height: 30,
					border: false
				}]
			});
			mp.render();
			
			ModuleBuilder.nextYear = new Date();
			ModuleBuilder.nextYear.setDate(ModuleBuilder.nextYear.getDate() + 360);
			
			var nextyear = ModuleBuilder.nextYear;
			
			if (Ck.getSub("ModuleBuilder", "helpHidden") == "true") {
				mp.getUnitByPosition('right').collapse();
			}
			if (Ck.getSub("ModuleBuilder", "treeHidden") == "true") {
				mp.getUnitByPosition('left').collapse();
			}
			
			var centerEl = mp.getUnitByPosition('center').get('wrap');
			tp.appendTo(centerEl);
			
			//YUI does not take the resizers into account when calculating panel size.
			var correctW = function(){
				var w = (this.body.offsetWidth - 7) + "px";
				this.body.style.width = w;
				this.header.style.width = w;
                if (typeof Studio2 != "undefined")
                    Studio2.resizeDivs();
                if (typeof resizeDDLists == "function")
                    resizeDDLists();
			};
			mp.getUnitByPosition('right').on("resize", correctW); 
			mp.getUnitByPosition('right').on("collapse", function(){
				Ck.setSub("ModuleBuilder", "helpHidden", "true");
                mp.get("element").querySelector(".yui-layout-clip-right .collapse").id = "expand_help";
			});
			mp.getUnitByPosition('right').on("expand", function(){
				Ck.setSub("ModuleBuilder", "helpHidden", "false");
			});
			mp.getUnitByPosition('left').on("resize", correctW);
			mp.getUnitByPosition('left').on("collapse", function(){
				Ck.setSub("ModuleBuilder", "treeHidden", "true");
                mp.get("element").querySelector(".yui-layout-clip-left .collapse").id = "expand_tree";
			});
			mp.getUnitByPosition('left').on("expand", function(){
				Ck.setSub("ModuleBuilder", "treeHidden", "false");
			});
			mp.resize(true);
			Event.on(window, 'resize', ModuleBuilder.autoSetLayout, this, true);
			
			var tree = ModuleBuilder.tree = createTreePanel(TREE_DATA, {
				id: 'mbTree'
			});
			tree.setCollapseAnim("TVSlideOut");
			tree.setExpandAnim("TVSlideIn");
			//tree.subscribe("labelClick", ModuleBuilder.handleTreeClick);
			tree.subscribe("clickEvent", ModuleBuilder.handleTreeClick);
			tree.render();
			
			//Setup Browser History
			var mbContent = YAHOO.util.History.getBookmarkedState('mbContent');
			
			if (ModuleBuilder.mode == 'mb') {
				mp.getUnitByPosition('left').header.firstChild.innerHTML = SUGAR.language.get('ModuleBuilder', 'LBL_SECTION_PACKAGES');
				mbContent = mbContent ? mbContent : 'module=ModuleBuilder&action=package&package=';
			}
			else if (ModuleBuilder.mode == 'studio') {
				ModuleBuilder.MBpackage = ''; // set to empty so other views can recognize that dealing with an deployed, rather than undeployed, module
				mp.getUnitByPosition('left').header.firstChild.innerHTML = SUGAR.language.get('ModuleBuilder', 'LBL_SECTION_MODULES');
				mbContent = mbContent ? mbContent :'module=ModuleBuilder&action=wizard';
			}
			//BEGIN SUGARCRM flav=ent ONLY
			else if (ModuleBuilder.mode == 'sugarportal') {
				mp.getUnitByPosition('left').header.firstChild.innerHTML =SUGAR.language.get('ModuleBuilder', 'LBL_SECTION_PORTAL');
				mbContent = mbContent ? mbContent : 'module=ModuleBuilder&action=wizard&portal=1';
			}
			//END SUGARCRM flav=ent ONLY
			else if (ModuleBuilder.mode == 'dropdowns') {
				mp.getUnitByPosition('left').header.firstChild.innerHTML = SUGAR.language.get('ModuleBuilder', 'LBL_SECTION_DROPDOWNS');
				mbContent = mbContent ? mbContent : 'module=ModuleBuilder&action=dropdowns';
			}
			else {
				mp.getUnitByPosition('left').collapse(false);
				mbContent = mbContent ? mbContent : 'module=ModuleBuilder&action=home';
			}

			YAHOO.util.History.register('mbContent', mbContent, ModuleBuilder.navigate);
            YAHOO.util.History.initialize("yui-history-field", "yui-history-iframe");
			ModuleBuilder.getContent(mbContent);
			
			if (SUGAR.themes.tempHideLeftCol) SUGAR.themes.tempHideLeftCol();
			ModuleBuilder.autoSetLayout();
			
			ModuleBuilder.tabPanel.on('activeTabChange', function(e) {
				ModuleBuilder.helpLoad( e.newValue.get("id") ) ;
			});
			
			if (Dom.get("HideHandle")){
				if (SUGAR.themes.tempHideLeftCol){
					SUGAR.themes.tempHideLeftCol();
					}
			}
            //We need to add ID's to the collapse buttons for automated testing
            Dom.getElementsByClassName("collapse", "div", mp.getUnitByPosition('left').header)[0].id = "collapse_tree";
            Dom.getElementsByClassName("collapse", "div", mp.getUnitByPosition('right').header)[0].id = "collapse_help";

		},
		//Empty layout manager
		layoutValidation: {
			popup_window: null,
			popup: function(){
				ModuleBuilder.layoutValidation.popup_window = new YAHOO.widget.SimpleDialog("emptyLayout", {
					width: "400px",
					draggable: true,
					constraintoviewport: true,
					modal: true,
					fixedcenter: true,
					text: SUGAR.language.get('ModuleBuilder', 'ERROR_MINIMUM_FIELDS'),
					bodyStyle: "padding:5px",
					buttons: [{
						text: SUGAR.language.get('ModuleBuilder', 'LBL_BTN_CLOSE'),
						isDefault:true,
						handler: function(){
							ModuleBuilder.layoutValidation.popup_window.hide()
						}
					}]
				});
				ModuleBuilder.layoutValidation.popup_window.render(document.body);
				ModuleBuilder.layoutValidation.popup_window.show();
			}
		},
		//Layout history manager
		history: {
			popup_window: false,
			reverted: false,
			params: { },
			browse: function(module, layout, subpanel){
				subpanel = subpanel ? subpanel : "";
				if (!module && ModuleBuilder.module != "undefined") {
					module = ModuleBuilder.module;
				}   
				if (!ModuleBuilder.history.popup_window) {
					ModuleBuilder.history.popup_window = new YAHOO.SUGAR.AsyncPanel('histWindow', {
						width: 300,
						draggable: true,
						close: true,
						constraintoviewport: true,
						fixedcenter: false
					});
				}
				var module_str = module;
				if(typeof SUGAR.language.languages['app_list_strings']['moduleList'][module] != 'undefined'){
					module_str = SUGAR.language.languages['app_list_strings']['moduleList'][module];
				} 
				ModuleBuilder.history.popup_window.setHeader( module_str + ' : ' + SUGAR.language.get('ModuleBuilder', 'LBL_' + layout.toUpperCase()) + SUGAR.language.get('ModuleBuilder', 'LBL_HISTORY_TITLE'));
				ModuleBuilder.history.popup_window.setBody("test");
				ModuleBuilder.history.popup_window.render(document.body);
				ModuleBuilder.history.params = {
					module: 'ModuleBuilder',
					histAction: 'browse',
					action: 'history',
					view_package: ModuleBuilder.MBpackage,
					view_module: module,
					view: layout,
					subpanel: subpanel
				};
				ModuleBuilder.history.popup_window.load(ModuleBuilder.paramsToUrl(ModuleBuilder.history.params));
				ModuleBuilder.history.popup_window.show();
				ModuleBuilder.history.popup_window.center();
			},
			preview: function(module, layout, id, subpanel) {
				var prevPanel =  ModuleBuilder.findTabById('preview:' + id);
				if (!prevPanel) {
					ModuleBuilder.history.params = {
						module: 'ModuleBuilder',
						histAction: 'preview',
						action: 'history',
						view_package: ModuleBuilder.MBpackage,
						view_module: module,
						view: layout,
						sid: id,
						subpanel: subpanel
					};
					prevPanel = new YAHOO.SUGAR.ClosableTab({
						dataSrc: Connect.url + "&" + ModuleBuilder.paramsToUrl(ModuleBuilder.history.params),
						label: SUGAR.language.get("ModuleBuilder", "LBL_MB_PREVIEW"),
						id: 'preview:' + id,
						scroll: true,
						cacheData: true,
						active :true
					}, ModuleBuilder.tabPanel);
					prevPanel.closable = true;
					ModuleBuilder.tabPanel.addTab(prevPanel);
				} else {
					ModuleBuilder.tabPanel.set("activeTab", prevPanel);
				}
				
			},
			revert: function(module, layout, id, subpanel){
				var prevTab = ModuleBuilder.tabPanel.getTabIndex("preview:" + id);
				if(prevTab) ModuleBuilder.tabPanel.removeTab(prevTab);
				
				ModuleBuilder.history.params = {
					module: 'ModuleBuilder',
					histAction: 'restore',
					action: 'history',
					view_package: ModuleBuilder.MBpackage,
					view_module: module,
					view: layout,
					sid: id,
					subpanel: subpanel
				}
				ModuleBuilder.asyncRequest(ModuleBuilder.history.params, function(){
					ModuleBuilder.history.reverted = true;
					ModuleBuilder.getContent(ModuleBuilder.contentURL);
					ModuleBuilder.state.isDirty = true;
				});
			},
			cleanup: function() {
				if (ModuleBuilder.history.reverted && ModuleBuilder.history.params.histAction) {
					ModuleBuilder.history.params.histAction = 'unrestore';
					ModuleBuilder.asyncRequest({params: ModuleBuilder.history.params});
				}
				ModuleBuilder.history.params = { };
				ModuleBuilder.history.reverted = false;
			},
			update: function() {
				if (ModuleBuilder.history.popup_window && ModuleBuilder.history.popup_window.cfg.getProperty("visible")) {
					var historyButton = YAHOO.util.Dom.get('historyBtn');
					if (historyButton) {
						historyButton.onclick();
					} else {
						ModuleBuilder.history.popup_window.hide();
					}
				}
			}
		},
		state: {
			isDirty: false,
			saving: false,
            hideFailedMesage: false,
			intended_view: {
				url: null,
				successCall: null
			},
			current_view: {
				url: null,
				successCall: null
			},
			save_url_for_current_view: null,
			popup_window: null,
			setupState: function(){
				//ModuleBuilder.state.popup();
				document.body.setAttribute("onclose", "ModuleBuilder.state.popup(); ModuleBuilder.state.popup_window.show()");
				return;
			},
			onSaveClick: function(){
				//set dirty = false
				//call the save method of the current view.
				//call the intended action.
				ModuleBuilder.state.isDirty = false;
				var saveBtn = document.getElementById("saveBtn");
				if (!saveBtn) {
					var mbForm = document.forms[1];
					if (mbForm)
						var mbButtons = mbForm.getElementsByTagName("input");
					if (mbButtons) {
						for (var button = 0; button < mbButtons.length; button++) {
							var name = mbButtons[button].getAttribute("name");
							if (name && (name.toUpperCase() == "SAVEBTN" || name.toUpperCase() == "LSAVEBTN")) {
								saveBtn = mbButtons[button];
								break;
							}
						}
					}
					else {
						alert("Could not find the save action for this view.");
					}
				}
				if (saveBtn) {
					//After the save call completes, load the next page
					ModuleBuilder.state.saving = true;
					eval(saveBtn.getAttributeNode('onclick').value);
				}
				ModuleBuilder.state.popup_window.hide();
			},
			onDontSaveClick: function(){
				//set dirty to false
				//call the intended action.
				ModuleBuilder.state.isDirty = false;
				ModuleBuilder.history.cleanup();
				ModuleBuilder.getContent(ModuleBuilder.state.intended_view.url, ModuleBuilder.state.intended_view.successCall);
				ModuleBuilder.state.popup_window.hide();
			},
			loadOnSaveComplete: function() {
				ModuleBuilder.state.saving = false;
				ModuleBuilder.getContent(ModuleBuilder.state.intended_view.url, ModuleBuilder.state.intended_view.successCall);
			},
			popup: function(){
                if(false == YAHOO.lang.isObject(ModuleBuilder.state.popup_window) || ModuleBuilder.state.popup_window.id != 'confirmUnsaved'){
                    ModuleBuilder.state.popup_window = new YAHOO.widget.SimpleDialog("confirmUnsaved", {
                     width: "400px",
                     draggable: true,
                     constraintoviewport: true,
                     modal: true,
                     fixedcenter: true,
                     text: SUGAR.language.get('ModuleBuilder', 'LBL_CONFIRM_DONT_SAVE'),
                     bodyStyle: "padding:5px",
                     buttons: [{
                        text: SUGAR.language.get('ModuleBuilder', 'LBL_BTN_DONT_SAVE'),
                        handler: ModuleBuilder.state.onDontSaveClick
                     }, {
                        text: SUGAR.language.get('ModuleBuilder', 'LBL_BTN_CANCEL'),
                        isDefault:true,
                        handler: function(){
                            ModuleBuilder.state.popup_window.hide()
                        }
                     },{
                        text: SUGAR.language.get('ModuleBuilder', 'LBL_BTN_SAVE_CHANGES'),
                        handler: ModuleBuilder.state.onSaveClick
                        }]
                    });
                    ModuleBuilder.state.popup_window.setHeader(SUGAR.language.get('ModuleBuilder', 'LBL_CONFIRM_DONT_SAVE_TITLE'));
                }
                if(ModuleBuilder.disablePopupPrompt != 1){
                    ModuleBuilder.state.popup_window.render(document.body);
                }else{
                    ModuleBuilder.state.onDontSaveClick();
                }
			}
		},
        copyFromView: function(module, layout){
            var url = ModuleBuilder.contentURL;
            ModuleBuilder.getContent(url+"&copyFromEditView=true");
             ModuleBuilder.contentURL = url;
            ModuleBuilder.state.intended_view.url = url;
            ModuleBuilder.state.isDirty = true;
        },
		//AJAX Navigation Functions
		navigate : function(url) {
			//Check if we are just registering the url
			if (url != ModuleBuilder.contentURL) {
				ModuleBuilder.getContent(url);
			}
		},
		getContent: function(url, successCall){
			if (!url) return;
			
			if (url.substring(0, 11) == "javascript:")
			{
				eval(url.substring(11));
				return;
			}
			
			//save a pointer to intended action
			ModuleBuilder.state.intended_view.url = url;
			ModuleBuilder.state.intended_view.successCall = successCall;
			if(ModuleBuilder.state.isDirty){ //prompt to save current data.
				//check if we are editing a property of the current view (such views open up in new tabs)
				//if so we leave the state dirty and return
				temp_url = url.toLowerCase();
				if(null == temp_url.match(/&action=editproperty/)){
					ModuleBuilder.state.popup();
					ModuleBuilder.state.popup_window.show();
					return;
				}

			}else{
				ModuleBuilder.state.current_view.url = url;
				ModuleBuilder.state.current_view.successCall = successCall;
			}
			
			ModuleBuilder.contentURL =  url;
			if (typeof(successCall) != 'function') {
				if (ModuleBuilder.callInProgress)
					return;
				ModuleBuilder.callInProgress = true;
				successCall = ModuleBuilder.updateContent;
			}
			ModuleBuilder.asyncRequest(url, successCall);
		},
		updateContent: function(o){
			ModuleBuilder.callInProgress = false;
			//Check if a save action was called and now we need to move-on
			if (ModuleBuilder.state.saving) {
				ModuleBuilder.state.loadOnSaveComplete();
				return;
			}
			ajaxStatus.flashStatus(SUGAR.language.get('app_strings', 'LBL_REQUEST_PROCESSED'), 2000);
			if(ModuleBuilder.checkForErrors(o))
                return false; 
			
			try {
			var ajaxResponse = YAHOO.lang.JSON.parse((o.responseText));
			} catch (err) {
				YAHOO.SUGAR.MessageBox.show({
                    title: SUGAR.language.get('ModuleBuilder', 'ERROR_GENERIC_TITLE'),
                    msg: o.responseText,
                    width: 500
                });
				return false;
			}
			
			
			if (ajaxResponse.tpl){
				var t = new YAHOO.SUGAR.Template(ajaxResponse.tpl);
				ModuleBuilder.ajaxData = ajaxResponse.data;
				ModuleBuilder.tabPanel.getTab(0).set(t.exec(ajaxResponse.data));
				SUGAR.util.evalScript(t.exec(ajaxResponse.data));
				return true;
			}
			
			for (var maj in ajaxResponse) {
				var name = 'mb' + maj;
				var comp = ModuleBuilder.mainPanel.getUnitById(maj);
				if (!comp) {
					var tabs = ModuleBuilder.tabPanel.get("tabs");
					for (i in tabs) {
						if (tabs[i].get && tabs[i].get("id") == maj)
						comp = tabs[i];
					}
				}
				
				if (name == 'mbwest') { //refresh package_tree!
					var tree = ModuleBuilder.tree;
					var root = tree.root;
					tree.maxAnim = 0;
					tree.collapseAll();
					while (root.hasChildren()) {
						tree.removeNode(root.children[0], true);
					}
					addChildNodes(root, ajaxResponse.west.content.tree_data);
					tree.maxAnim = 2;
					tree.render();
				}
				else {
					if (!comp) {
						if(ajaxResponse[maj].action == 'deactivate') continue;
						comp = new YAHOO.SUGAR.ClosableTab({
							content: "<div class='bodywrapper'><script>ModuleBuilder.scriptTest=true;</script>" + ((maj == 'center') ? "<div>" + ajaxResponse[maj].crumb + "</div>" :"")
								 + ajaxResponse[maj].content + "</div>",
							label: ajaxResponse[maj].title,
							id: maj,
							scroll: true,
							closable: true,
							active :true
						}, ModuleBuilder.tabPanel);
						comp.closable = true;
						ModuleBuilder.scriptTest = false;
						ModuleBuilder.tabPanel.set("activeTab", comp);
						ModuleBuilder.tabPanel.addTab(comp);
						//Text if the browser automatically evaluated the content's script tags or not. If not, manually evaluate them.
						if (!ModuleBuilder.scriptTest)
							SUGAR.util.evalScript(ajaxResponse[maj].content);
					} else {
						//Store Center pane changes in browser history
						YAHOO.util.History.navigate('mbContent', ModuleBuilder.contentURL);
						if (name == 'mbcenter') {
							ModuleBuilder.closeAllTabs();
							comp = ModuleBuilder.tabPanel.getTab(0);
						}
						ModuleBuilder.tabPanel.set("activeTab", comp);
						comp.set('content', "<div class='bodywrapper'><div>" + ajaxResponse[maj].crumb + "</div>" + ajaxResponse[maj].content + "</div>");
						if (ajaxResponse[maj].title != "no_change")
							comp.set('label', ajaxResponse[maj].title);
						SUGAR.util.evalScript(ajaxResponse[maj].content);	
					}
				}
				ModuleBuilder.history.update();
			}
		},
		checkForErrors: function(o){
			if (SUGAR.util.isLoginPage(o.responseText))
				return true;
			if (o.responseText.substr(0, 1) == "<") {
                YAHOO.SUGAR.MessageBox.show({
					title: SUGAR.language.get('ModuleBuilder', 'ERROR_GENERIC_TITLE'),
					msg: o.responseText,
					width: 500
				});
				return true;
            }
			
			
			return false;
		},
		submitForm: function(formname, successCall){
			ajaxStatus.showStatus(SUGAR.language.get('ModuleBuilder', 'LBL_AJAX_LOADING'));
			if (typeof(successCall) == 'undefined') {
				successCall = ModuleBuilder.updateContent;
			}
			else {
				ModuleBuilder.callLock = true;
			}
			Connect.setForm(document.getElementById(formname) || document.forms[formname]);
			Connect.asyncRequest(
			    Connect.method, 
			    Connect.url, 
			    {success: successCall, failure: ModuleBuilder.failed}
			);
		},
		setMode: function(reqMode){
			ModuleBuilder.mode = reqMode;
		},
		main: function(type){
			document.location.href = 'index.php?module=ModuleBuilder&action=index&type=' + type;
		},
		failed: function(o){
            if(!ModuleBuilder.state.hideFailedMesage){
                ajaxStatus.flashStatus(SUGAR.language.get('ModuleBuilder', 'LBL_AJAX_FAILED_DATA'), 2000);
            }
		},
		//Wizard Functions
		buttonDown: function(button, name, list){
			if (typeof(name) != 'undefined') {
				for (i in ModuleBuilder.buttons[list]) {
					ModuleBuilder.buttons[list][i].className = 'wizardButton';
				}
				ModuleBuilder.buttonSelect(name, list);
			}
			button.className = 'wizardButtonDown';

		},
		buttonOver: function(button){
			button.className = 'button';
		},
		buttonOut: function(button, name, list){
			if (typeof(name) != 'undefined') {
				if (ModuleBuilder.buttonGetSelected(list) != name) {
					button.className = 'wizardButton'
				}
			}
			else {
				button.className = 'wizardButton'
			}

		},
		buttonAdd: function(id, name, list){
			if (typeof(ModuleBuilder.buttons[list]) == 'undefined') {
				ModuleBuilder.buttons[list] = {};

			}
			ModuleBuilder.buttons[list][name] = document.getElementById(id);

		},
		buttonGetSelected: function(list){
			if (typeof(ModuleBuilder.selected[list]) == 'undefined') {
				return false;
			}
			return ModuleBuilder.selected[list];
		},
		buttonSelect: function(name, list){
			ModuleBuilder.selected[list] = name;
		},
		buttonToForm: function(form, field, list){
			var theField = eval('document.' + form + '.' + field);
			theField.value = ModuleBuilder.buttonGetSelected(list);
		},

		getTitle: function(title, breadCrumb){
			return "<h2>" + title + "</h2><br>" + breadCrumb;
		},
		closeAllTabs: function() {
			var tabs = ModuleBuilder.tabPanel.get('tabs');
			for (var i = tabs.length - 1; i > -1; i--) {
				var tab = tabs[i];
				if (tab.close) {
					tab.close();
				}
			}
		},
		//Help Functions
		helpRegister: function(name){
			var formname = 'document.' + name;
			var form = eval(formname);
			var i = 0;
			for (i = 0; i < form.elements.length; i++) {
				if (typeof(form.elements[i].type) != 'undefined' && typeof(form.elements[i].name) != 'undefined' && form.elements[i].type != 'hidden') {
					form.elements[i].onmouseover = function(){
						ModuleBuilder.helpToggle(this.name)
					};
					form.elements[i].onmouseout = function(){
						ModuleBuilder.helpToggle('default')
					};

				}
			}
		},
		helpUnregisterByID: function (id){
			var elm = document.getElementById(id);
			if (elm) {
			elm.onmouseover = function() {};
			elm.onmouseout = function() {};
			}
			return;
		},
		helpRegisterByID: function(name, tag){
			var parent = document.getElementById(name);
			var children = parent.getElementsByTagName(tag);
			for (var i = 0; i < children.length; i++) {
				if (children[i].id != 'undefined') {
					children[i].onmouseover = function(){
						ModuleBuilder.helpToggle(this.id)
					};
					//children[i].onmouseover = function(){alert(this.id)};
					children[i].onmouseout = function(){
						ModuleBuilder.helpToggle('default')
					};
				}
			}
		},
		helpSetup: function(group, def, panel){
			if (!ModuleBuilder.panelHelp) ModuleBuilder.panelHelp = [];
			
			// setup the linkage between this tab/panel and the relevant help
			var id = ModuleBuilder.tabPanel.get("activeTab").get("id")  ;
			ModuleBuilder.panelHelp [ id ] = { lang: group , def: def } ;
			 
			// get the help text if required
			if ( ! ModuleBuilder.AllHelpLang ) ModuleBuilder.AllHelpLang = SUGAR.language.get('ModuleBuilder', 'help');

			if (group && def) {
				ModuleBuilder.helpLang = ModuleBuilder.AllHelpLang[group];
				ModuleBuilder.helpDefault = def;
			} 
			
			ModuleBuilder.helpToggle('default');
		},
		helpLoad: function(panelId){
			if (!ModuleBuilder.panelHelp) return;
			
			if ( ! ModuleBuilder.AllHelpLang ) ModuleBuilder.AllHelpLang = SUGAR.language.get('ModuleBuilder', 'help');
			
			if ( ModuleBuilder.panelHelp [ panelId ] )
			{
				ModuleBuilder.helpLang = ModuleBuilder.AllHelpLang[ ModuleBuilder.panelHelp [ panelId ].lang ];
				ModuleBuilder.helpDefault = ModuleBuilder.panelHelp [ panelId ].def ;
				ModuleBuilder.helpToggle('default');
			}
		},
		helpToggle: function(name){
			if (name == 'default')
				name = ModuleBuilder.helpDefault;
			if (ModuleBuilder.helpLang != null && typeof(ModuleBuilder.helpLang[name]) != 'undefined') {
				document.getElementById('mbhelp').innerHTML = ModuleBuilder.helpLang[name];
			}
		},
		handleSave: function(form, callBack){
			if (check_form(form)) {
				ModuleBuilder.state.isDirty=false;
				ModuleBuilder.submitForm(form, callBack);
			}
		},
		//Tree Functions
		handleTreeClick: function(o) {
			var node = o.node;
			ModuleBuilder.getContent(node.data.action);
			return false;
		},
		treeSubscribe:function(tree){
			tree.subscribe("labelClick", ModuleBuilder.treeLabelClick);
		},
		treeRefresh:function(type){
			ModuleBuilder.getContent('module=ModuleBuilder&action=ViewTree&tree=' + type);
		},
		//MB Specific
		addModule: function(MBpackage){
			ModuleBuilder.getContent('module=ModuleBuilder&action=module&view_package=' + MBpackage);
		},
		viewModule: function(MBpackage, module){
			ModuleBuilder.getContent('module=ModuleBuilder&action=module&view_package=' + MBpackage + '&view_module=' + module);
		},
		packageDelete: function(MBpackage){
			ajaxStatus.showStatus(SUGAR.language.get('ModuleBuilder', 'LBL_AJAX_DELETING'));
			if (confirm(SUGAR.language.get('ModuleBuilder', 'LBL_JS_REMOVE_PACKAGE'))) {
				ModuleBuilder.getContent('module=ModuleBuilder&action=DeletePackage&package=' + MBpackage);
				var node = ModuleBuilder.tree.getNodeByProperty('id', 'package_tree/' + MBpackage);
				if (node) ModuleBuilder.tree.removeNode(node, true);
			}
		},
		packagePublish: function(form){
			if (check_form(form)) {
				ajaxStatus.showStatus(SUGAR.language.get('ModuleBuilder', 'LBL_AJAX_BUILDPROGRESS'));
				ModuleBuilder.submitForm(form, ModuleBuilder.packageBuild);
			}
		},
		packageBuild: function(o){
			//make sure no user changes were made
			document.CreatePackage.action.value = 'BuildPackage';
			document.CreatePackage.submit();
			ajaxStatus.flashStatus(SUGAR.language.get('app_strings', 'LBL_REQUEST_PROCESSED'), 2000);
			ModuleBuilder.callLock = false;
		},
		packageDeploy: function(form, deployed){
            var confirmed = true;
            if (deployed){
    			confirmed = confirm(SUGAR.language.get('ModuleBuilder', 'LBL_JS_DEPLOY_PACKAGE'));
            }
	        if (confirmed && check_form(form)) {
				ajaxStatus.showStatus(SUGAR.language.get('ModuleBuilder', 'LBL_AJAX_DEPLOYPROGRESS'));
				ModuleBuilder.submitForm(form, ModuleBuilder.packageInstall);
			}
		},
		packageInstall: function(o){
			//make sure no user changes were made
			document.CreatePackage.action.value = 'displaydeploy';
			ModuleBuilder.callLock = false;
			ModuleBuilder.submitForm('CreatePackage', ModuleBuilder.packageInstallCleanup);
		},
		packageInstallCleanup: function(o){
			//make sure no user changes were made
			document.CreatePackage.action.value = 'displaydeploy';
			ModuleBuilder.callLock = false;
			ModuleBuilder.submitForm('CreatePackage');
		},
		beginDeploy: function(p){
			ModuleBuilder.asyncRequest('module=ModuleBuilder&action=DeployPackage&package=' + p, ModuleBuilder.deployComplete);
		},
		deployComplete: function(o){
			var resp = o.responseText;
			
			//check if the deploy completed
			if (!resp.match(/^\s*(\s*(Table already exists : [\w_]*)(<br>)*\s*)*complete$/m))
			{
					//Unknown error occured, warn the user
					alert(SUGAR.language.get("ModuleBuilder", "LBL_DEPLOY_FAILED"));
			}
			//Cleanup in the background
			ModuleBuilder.asyncRequest(
			    'module=Administration&action=RebuildRelationship&silent=true',
				function(){}
			);
			ModuleBuilder.asyncRequest(
				'module=Administration&action=RebuildDashlets&silent=true',
				function(){}			
			);
			
			ModuleBuilder.failed = function(){};
            ModuleBuilder.state.hideFailedMesage = true;
			//Reload the page
			window.setTimeout("window.location.assign(window.location.href.split('#')[0])", 2000);
			
			
		},
		packageExport: function(form){
			if (check_form(form)) {
				ajaxStatus.showStatus(SUGAR.language.get('ModuleBuilder', 'LBL_AJAX_BUILDPROGRESS'));
				ModuleBuilder.submitForm(form, ModuleBuilder.packageExportProject);
			}
		},
		packageExportProject: function(o){
			//make sure no user changes were made
			document.CreatePackage.action.value = 'ExportPackage';
			document.CreatePackage.submit();
			ajaxStatus.flashStatus(SUGAR.language.get('app_strings', 'LBL_REQUEST_PROCESSED'), 2000);
			ModuleBuilder.callLock = false;
		},
		moduleDelete: function(MBpackage, module){
			ajaxStatus.showStatus(SUGAR.language.get('ModuleBuilder', 'LBL_AJAX_DELETING'));
			if (confirm(SUGAR.language.get('ModuleBuilder', 'LBL_JS_REMOVE_MODULE'))) {
				ModuleBuilder.getContent('module=ModuleBuilder&action=DeleteModule&package=' + MBpackage + '&view_module=' + module);
				var node = ModuleBuilder.tree.getNodeByProperty('id', 'package_tree/' + MBpackage + '/' + module);
				if (node) ModuleBuilder.tree.removeNode(node, true);
			}
		},
		moduleViewFields: function(o){

			ModuleBuilder.callLock = false;

			ModuleBuilder.getContent('module=ModuleBuilder&action=modulefields&view_package=' + ModuleBuilder.MBpackage + 
				'&view_module=' + ModuleBuilder.module);
		},
		moduleLoadField: function(name, type){
			if (typeof(type) == 'undefined')
				type = 0;
			if (typeof(formsWithFieldLogic) != 'undefined')
				formsWithFieldLogic = 'undefined';
			ModuleBuilder.getContent('module=ModuleBuilder&action=modulefield&view_package=' + ModuleBuilder.MBpackage + 
				'&view_module=' + ModuleBuilder.module + '&field=' + name + '&type=' + type);
		},
		moduleLoadLabels: function(type){
			if (typeof(type) == 'undefined')
				type = 0;
			else
				if (type == "studio") {
					ModuleBuilder.getContent('module=ModuleBuilder&action=editLabels&view_module=' + ModuleBuilder.module);
				}
				else
					if (type == "mb") {
						ModuleBuilder.getContent('module=ModuleBuilder&action=modulelabels&view_package=' + ModuleBuilder.MBpackage + '&view_module=' + ModuleBuilder.module + '&type=' + type);
					}
		},
		moduleViewRelationships: function(o){
			ModuleBuilder.callLock = false;
			ModuleBuilder.getContent('module=ModuleBuilder&action=relationships&view_package=' + ModuleBuilder.MBpackage + '&view_module=' + ModuleBuilder.module);
		},
		moduleLoadRelationship2: function(name, resetLabel, checkLanguage) {
			if (resetLabel && Dom.get('rhs_label')) {
				Dom.get('rhs_label').value = "";
			}
			var panel = ModuleBuilder.findTabById('relEditor');
			if (!panel) {
				panel = new YAHOO.SUGAR.ClosableTab({
					label: SUGAR.language.get("ModuleBuilder", "LBL_RELATIONSHIP_EDIT"),
					id: 'relEditor',
					scroll: true,
					cacheData: true,
					active :true
				}, ModuleBuilder.tabPanel);
				ModuleBuilder.tabPanel.addTab(panel);
			} else {
				ModuleBuilder.tabPanel.set("activeTab", panel);
			}
			var rtField = Dom.get('relationship_type_field');
			var relType = rtField ? rtField.options[rtField.selectedIndex].value: "";
			if (name == "") {
				name = Dom.get('rel_name_id') ? Dom.get('rel_name_id').value : "";
			}
			var params = {
				module: 'ModuleBuilder',
				action: 'relationship',
				view_package: ModuleBuilder.MBpackage,
				view_module: ModuleBuilder.module,
				relationship_name: name,
				relationship_type: relType,
				lhs_module: Dom.get('lhs_mod_field') ? Dom.get('lhs_mod_field').value : document.forms.relform ? document.forms.relform.lhs_module.value : "",
				rhs_module: Dom.get('rhs_mod_field') ? Dom.get('rhs_mod_field').value : "",
				lhs_label:  Dom.get('lhs_label')     ? Dom.get('lhs_label').value     : "",
				rhs_label:  Dom.get('rhs_label')     ? Dom.get('rhs_label').value     : "",
				json: false,
				id:'relEditor'
			};
			if(checkLanguage){
				params['relationship_lang'] = Dom.get('relationship_lang').value;
				params['ajaxLoad'] = '1';
			}
			ModuleBuilder.asyncRequest(params, function(o) {
				ajaxStatus.hideStatus();
				var tab = ModuleBuilder.findTabById('relEditor');
				tab.set("content", o.responseText);
				SUGAR.util.evalScript(o.responseText);
			});
		},
		moduleDropDown: function(name, field){
			ModuleBuilder.getContent('module=ModuleBuilder&action=dropdown&view_package=' + ModuleBuilder.MBpackage + '&view_module=' + ModuleBuilder.module + '&dropdown_name=' + name + '&field=' + field);
		},
		moduleViewLayouts: function(o){
			ModuleBuilder.callLock = false;
			ModuleBuilder.getContent('module=ModuleBuilder&MB=1&action=wizard&view_package=' + ModuleBuilder.MBpackage + '&view_module=' + ModuleBuilder.module);
		},
		findTabById : function(id) {
			var tabs = ModuleBuilder.tabPanel.get("tabs");
			for (var i = 0; i < tabs.length; i++) {
				if (tabs[i].get("id") == id)
					return tabs[i];
			}
			return null;
		}, 
		autoSetLayout: function(){
			var mp = ModuleBuilder.mainPanel;
			var c = Dom.get("mblayout");
			mp.set("height", Dom.getViewportHeight() - Dom.getY(c) - 30);
			mp.set("width", Dom.getViewportWidth() - 40);
			mp.resize(true);
			var tabEl = ModuleBuilder.tabPanel.get("element");
			Dom.setStyle(tabEl.firstChild.nextSibling, "overflow-y", "auto");
			Dom.setStyle(tabEl.firstChild.nextSibling, "height", tabEl.offsetHeight - ModuleBuilder.tabPanel.get("element").firstChild.offsetHeight - 5 + "px");
			//Resize editor layouts
			if (document.getElementById('toolbox')) Studio2.resizeDivs();
			if (document.getElementById('edittabs')) resizeDDLists();
		},
		paramsToUrl : function (params) {
			url = "";
			for (i in params) {
                url += i + "=" + params[i] + "&";
			}
			return url;
		},
		asyncRequest : function(params, callback) {
			var url;
			if (typeof params == "object") {
				url = ModuleBuilder.paramsToUrl(params);
			} else {
				url = params;
			}
			ajaxStatus.showStatus(SUGAR.language.get('app_strings', 'LBL_LOADING_PAGE'));
			Connect.asyncRequest(
			    Connect.method, 
			    Connect.url + '&' + url, 
			    {success: callback, failure: ModuleBuilder.failed}
			);
		},
		refreshGlobalDropDown: function(o){
			// just clear the callLock; the convention is that this is done in a handler rather than in updateContent
			ModuleBuilder.callLock = false;
			ModuleBuilder.updateContent(o);
		},
		refreshDropDown: function(){
			ModuleBuilder.callLock = false;
			document.popup_form.action.value = 'RefreshField';
			document.popup_form.new_dropdown.value = ModuleBuilder.refreshDD_name;
			SimpleList.refreshDD_name = '';
			ModuleBuilder.submitForm("popup_form");
		},
		dropdownChanged: function(value){
			var select = document.getElementById('default[]').options;
			while(select.length > 0) {
				select[0] = null;
			}
			ModuleBuilder.asyncRequest(
				'module=ModuleBuilder&action=get_app_list_string&key=' + value +
				'&view_package=' + ModuleBuilder.MBpackage + '&view_module=' + ModuleBuilder.module,
				ModuleBuilder.dropdownChangedCallback
			);
		},
		dropdownChangedCallback : function(o) {
			var ajaxResponse = YAHOO.lang.JSON.parse(o.responseText);
			var select = document.getElementById('default[]').options;
			var count = 0;
			for (var key in ajaxResponse) {
				select[count] = new Option(ajaxResponse[key], key);
				count++;
			}
			ajaxStatus.flashStatus(SUGAR.language.get('app_strings', 'LBL_REQUEST_PROCESSED'), 2000);
		},
		setSelectedOption : function (sel, option)
		{
			var sel = Dom.get(sel);
			for (var i = 0; i < sel.options.length; i++)
			{
				if(sel.options[i].value == option) {
					sel.selectedIndex = i;
					return true;
				}
			}
			return false;
		}
		//BEGIN SUGARCRM flav=pro ONLY
		,moduleLoadFormula: function(formula, targetId, returnType){
            if (!targetId)
                targetId = "formula";
            if(!returnType)
                returnType = "";

            if (!ModuleBuilder.formulaEditorWindow)
                ModuleBuilder.formulaEditorWindow = new YAHOO.SUGAR.AsyncPanel('formulaBuilderWindow', {
                    width: 512,
                    draggable: true,
                    close: true,
                    constraintoviewport: true,
                    fixedcenter: false,
                    script: true,
                    modal: true
                });
            var win = ModuleBuilder.formulaEditorWindow;
            win.setHeader(SUGAR.language.get("ModuleBuilder", "LBL_FORMULA_BUILDER"));
            win.setBody("loading...");
            win.render(document.body);
            win.params = {
                module:"ExpressionEngine",
                action:"editFormula",
                targetField: targetId,
                returnType: returnType,
                loadExt:false,
                embed: true,
                targetModule:ModuleBuilder.module,
                package:ModuleBuilder.MBpackage,
                formula:encodeURIComponent(YAHOO.lang.JSON.stringify(formula))
            };
            win.load(ModuleBuilder.paramsToUrl(win.params), null, function()
            {
                ModuleBuilder.formulaEditorWindow.center();
                SUGAR.util.evalScript(ModuleBuilder.formulaEditorWindow.body.innerHTML);
            });
            win.show();
            win.center();
        },
        editVisibilityGrid: function(targetId, parent_options, child_options){
            if (!targetId)
                targetId = "visibility_grid";

            if (!ModuleBuilder.visGridWindow)
                ModuleBuilder.visGridWindow = new YAHOO.SUGAR.AsyncPanel('visGridWindow', {
                    draggable: true,
                    close: true,
                    constraintoviewport: true,
                    fixedcenter: false,
                    script: true,
                    modal: true,
                    buttons: [
                        {text:"Save", handler:function(){

                        }, isDefault:true },
                        { text:"Cancel",  handler:function(){
                            ModuleBuilder.visGridWindow.hide()}
                        }
                    ]
                });
            var win = ModuleBuilder.visGridWindow;
            win.setHeader(SUGAR.language.get("ModuleBuilder", "LBL_VISIBILITY_EDITOR"));
            win.setBody("loading...");
            win.render(document.body);
            win.params = {
                module:"ModuleBuilder",
                action:"depdropdown",
                targetModule: ModuleBuilder.module,
                package: ModuleBuilder.MBpackage,
                parentList: parent_options,
                childList: child_options,
                targetId:targetId,
                mode:2,
                mapping: Dom.get(targetId).value
            };
            win.load(ModuleBuilder.paramsToUrl(win.params), "POST", function()
            {
                SUGAR.util.evalScript(win.body.innerHTML);
                //firefox will ignore the left panel size, so we need to manually force the windows height and width
                win.body.style.height = "570px";
                win.body.style.minWidth = "780px";
                win.center();
            });
            win.show();
            win.center();
        },
        toggleParent: function(enable){
            if (typeof(enable) == 'undefined') {
                enable = Dom.get('has_parent').checked;
            }
            var display = enable ? "" : "none";
            Dom.setStyle("visGridRow", "display", display);
            if(Dom.get('has_parent')){
                Dom.get('has_parent').value = enable;
            }
        },
		toggleCF: function(enable) {
            if (typeof(enable) == 'undefined') {
                enable = Dom.get('calculated').checked;
            }
            var display = enable ? "" : "none";
			Dom.setStyle("formulaRow", "display", display);
			Dom.setStyle("enforcedRow", "display", display);
            if(Dom.get('calculated')){
			    Dom.get('calculated').value = enable;
            }
            this.toggleEnforced(enable);
        },
        toggleEnforced: function(enable) {
            if (typeof enable == "undefined")
                enable = Dom.get("enforced").checked && Dom.get('calculated').checked;
            var reportable = Dom.get('reportable');
            var importable = Dom.get('importable');
            var duplicate  = Dom.get('duplicate_merge');
			var massupdate  = Dom.get('massupdate');
			//Getting the default value field is tricky as it can have multiple different ID's
			var defaultVal = false;
			for(var i in {'default':"", 'int_default':"", 'default[]':""})
				if (Dom.get(i)){defaultVal = Dom.get(i); break;}

            var disable = enable ? true : "";
            if (reportable) reportable.disabled = disable;
            if(enable)
            {
            	if (duplicate)ModuleBuilder.setSelectedOption(duplicate, '0')

            	if (importable)ModuleBuilder.setSelectedOption(importable, 'false');
            }
            if (importable)importable.disabled = disable;
            if (duplicate)duplicate.disabled = disable;
			if (massupdate)massupdate.disabled = disable;
			this.toggleDateTimeDefalutEnabled(disable);
			if (defaultVal) defaultVal.disabled = disable;
            if(Dom.get("enforced")){
                Dom.get("enforced").value = enable;
            }
        },
		toggleDateTimeDefalutEnabled : function(disable)
		{
			if (Dom.get("defaultDate_date"))
			{
				Dom.get("defaultDate_date").disabled = disable;
				Dom.get("defaultTime_hours").disabled = disable;
				Dom.get("defaultTime_minutes").disabled = disable;
                if (Dom.get("defaultTime_meridiem"))
			    {
				    Dom.get("defaultTime_meridiem").disabled = disable;
                }
			}
		},
        toggleDF: function(enable, query) {
            if (typeof(enable) == 'undefined' || enable === null) {
                enable = Dom.get('dependent').checked;
            }
            var display = enable ? "" : "none";
            Dom.setStyle('visFormulaRow', 'display', display);
            //If a query was passed in, we need to enble/disable elements that match the query as well
            if (query)
                $(query).css("display", display);
            Dom.get('dependency').disabled = !enable;
			Dom.get('dependent').value = enable;
        },
        //We can only have a formula or a vis_grid. Before we save we need to clear the one we aren't using
        validateDD: function() {
            if ($('#depTypeSelect').val() != "parent")
                $("#visibility_grid").val("");
            if ($('#depTypeSelect').val() != "formula")
                $("#dependency").val("");
            return true;
        },
        addToHead: function(src, type){
            var tag, srcKey, typeTag, rel;
            type = type ? type : "js";
            if (type == "js") {
                tag = "script";
                srcKey = "src";
                typeTag = "text/javascript";
            } else if (type == "css")
            {
                tag = "link";
                srcKey = "href";
                typeTag = "text/css";
                rel = "stylesheet";
            } else {
                //Invalid or unknown type
                return;
            }
            var headElem = document.getElementsByTagName('head')[0];
            var tmpElem = document.createElement(tag);
            tmpElem.type = typeTag;
            tmpElem[srcKey] = src;
            if (rel)
                tmpElem.rel = rel;
            headElem.appendChild(tmpElem);
        }
		//END SUGARCRM flav=pro ONLY
        //BEGIN SUGARCRM flav=een ONLY
        ,moduleLoadVisibility: function(fieldname, options, trigger , targetId){
            var EditorWindow = new Ext.Window({
                id: 'visibilityEditorWindow',
                title: SUGAR.language.get('ModuleBuilder','LBL_SECTION_VISIBILITY_EDITOR' ),
                autoLoad: {
                    url:"index.php",
                    params: {
                        embed: true,
                        module:"ModuleBuilder",
                        action:"visibilityeditor",
                        onSave:"function(){Ext.getDom('"+ targetId + "').value = Ext.getCmp('visibilityEditor').commit();Ext.getCmp('visibilityEditorWindow').close();}",
                        //onLoad:"function(){Ext.getCmp('visibilityEditorWindow').center();}",
                        onClose:"function(){Ext.getCmp('visibilityEditorWindow').close();}",
                        view_module:ModuleBuilder.module,
                        view_package:ModuleBuilder.MBpackage,
                        field:fieldname,
                        options:options,
                        trigger:trigger
                    },
                    scripts: true
                },
                modal:true,
                plain:true,
                resizable:true,
                nodyBorder:false,
                width:800,
                autoHeight:true
            });
            EditorWindow.show();
        }
        //END SUGARCRM flav=een ONLY
	};
	ModuleBuilder.buttons = {};
	ModuleBuilder.selected = {};
	ModuleBuilder.callLock = false;
}
})();