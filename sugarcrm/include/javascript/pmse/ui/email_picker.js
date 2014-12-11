/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
var EmailPickerField = function (settings, parent) {
	MultipleItemField.call(this, settings, parent);
	this._teams = null;
	this._teamsPanel = null;
	/*this._fieldsPanel = null;*/
	this._suggestPanel = null;
	this._suggestTimer = null;
	this._delaySuggestTime = null;
	this._suggestionDataURL = null;
	this._suggestionDataRoot = null;
	this._suggestionItemName = null;
	this._suggestionItemAddress = null;
	this._relatedModulesFieldsDataURL = null;
	this._relatedModulesFieldsDataRoot = null;
	this._suggestionVisible = false;
	this._teamNameField = null;
	this._lastQuery = null;
	EmailPickerField.prototype.init.call(this, settings);
};

EmailPickerField.prototype = new MultipleItemField();
EmailPickerField.prototype.constructor = EmailPickerField;
EmailPickerField.prototype.type = 'EmailPickerField';

EmailPickerField.prototype.init = function (settings) {
	var defaults = {
		teams: [],
		delaySuggestTime: 500,
		suggestionDataURL: null,
		suggestionDataRoot: null,
		suggestionItemName: null,
		suggestionItemAddress: "email"/*,
		relatedModulesFieldsDataURL: null,
		relatedModulesFieldsDataRoot: null*/,
		teamNameField: "name"
	};

	jQuery.extend(true, defaults, settings);

	this._lastQuery = {};

	this.setTeamNameField(defaults.teamNameField)
		.setTeams(defaults.teams)
		.setSuggestionDataURL(defaults.suggestionDataURL)
		.setSuggestionDataRoot(defaults.suggestionDataRoot)
		.setDelaySuggestTime(defaults.delaySuggestTime)
		.setSuggestionItemName(defaults.suggestionItemName)
		.setSuggestionItemAddress(defaults.suggestionItemAddress)/*
		.setVariables(defaults.variables)
		.setRelatedModulesFieldsDataURL(defaults.relatedModulesFieldsDataURL)
		.setRelatedModulesFieldsDataRoot(defaults.relatedModulesFieldsDataRoot)*/;
};

/*EmailPickerField.prototype.setRelatedModulesFieldsDataURL = function (url) {
	if (!(url === null || typeof url === "string")) {
		throw new Error("setRelatedModulesFieldsDataURL(): The parameter must be a string or null.");
	}
	this._relatedModulesFieldsDataURL = url;
	return this;
};

EmailPickerField.prototype.setRelatedModulesFieldsDataRoot = function (root) {
	if (!(root === null || typeof root === 'string')) {
		 throw new Error("setRelatedModulesFieldsDataRoot(): the parameter must be a string or null.");
	}
	this._relatedModulesFieldsDataRoot = root;
	return this;
};*/

EmailPickerField.prototype.setTeamNameField = function(teamNameField) {
	if (typeof teamNameField !== 'string') {
		throw new Error("setTeamNameField(): The parameter must be a string.");
	}
	this._teamNameField = teamNameField;
	return this;
};

EmailPickerField.prototype.setSuggestionItemName = function(text) {
	if(!(text === null || typeof text === 'string')) {
		throw new Error("setSuggestionItemName(): The parameter must be a string or null.");
	}
	this._suggestionItemName = text;
	return this;
};

EmailPickerField.prototype.setSuggestionItemAddress = function(text) {
	if(!(text === null || (typeof text === 'string' && text !== ""))) {
		throw new Error("setSuggestionItemAddress(): The parameter must be a string different than an empty string.");
	}
	this._suggestionItemAddress = text;
	return this;
};

EmailPickerField.prototype.setSuggestionDataURL = function (url) {
	if (!(url === null || typeof url === "string")) {
		throw new Error("setSuggestionDataURL(): The parameter must be a string or null.");
	}
	this._suggestionDataURL = url;
	return this;
};

EmailPickerField.prototype.setSuggestionDataRoot = function(root) {
	if (!(root === null || typeof root === "string")) {
		throw new Error("setSuggestionDataRoot(): The parameter must be a string or root.");
	}
	this._suggestionDataRoot = root;
	return this;
};

EmailPickerField.prototype.setDelaySuggestTime = function (milliseconds) {
	if (typeof milliseconds !== "number") {
		throw new Error("setDelaySuggestTime(): The parameter must be a number.");
	}
	this._delaySuggestTime = milliseconds;
	return this;
};

EmailPickerField.prototype.setTeams = function (teams) {
	var i;
	if(!jQuery.isArray(teams)) {
		throw new Error("setItems(): The parameter must be an array.");
	}
	this._teams = teams;
	if(this._teamsPanel) {
		this._teamsPanel.setDataItems(this._teams);
		this._teamsPanel.setVisible(this._teams.length);
	}
	return this;
};

EmailPickerField.prototype._onItemSetText = function () {
	return function(itemObject, data) {
		return data.name || data.emailAddress || "";
	};
};

EmailPickerField.prototype._createItemData = function(data) {
	return {
		name: data.name || data.emailAddress || "",
		emailAddress: data.emailAddress || "",
		module: null
	};
};

EmailPickerField.prototype._onBeforeAddItemByInput = function () {
	var that = this;
	return function (itemContainer, singleItem, text, index) {
		return that._createItem({
			emailAddress: text
		}, singleItem);
	}
};

EmailPickerField.prototype._onPanelValueGeneration = function () {
	var that = this;
	return function (fieldPanel, fieldPanelItem, data) {
		var newEmailItemm;
		if(fieldPanelItem.type === "FieldPanelButton") {
			newEmailItem = that._createItem({
				name: data.text,
				emailAddress: data.value
			});
		} else {
			switch(fieldPanelItem.id) {
				case "list-teams":
					newEmailItem = that._createItem({
						emailAddress: "Team",
						name: data[that._teamNameField]
					});
					break;
				default:
					newEmailItem = that._createItem({
						emailAddress: data[that._suggestionItemAddress],
						name: data[that._suggestionItemName || that._suggestionItemAddress] 
					});
			}
		}
		that.controlObject.addItem(newEmailItem, that.controlObject.getSelectedIndex());
	};
};

EmailPickerField.prototype._suggestionItemContent = function() {
	var that = this;
	return function (item, data) {
		var name = that.createHTMLElement('strong'),
			address = that.createHTMLElement('small'),
			container = that.createHTMLElement('a');

		container.href = "#";
		container.className = "adam email-picker-suggest";
		if(that._suggestionItemName) {
			name.className = "adam email-picker-suggest-name";
			name.textContent = data[that._suggestionItemName];
			container.appendChild(name);
		}
		address.className = "adam email-picker-suggest-address";
		address.textContent = data[that._suggestionItemAddress];
		container.appendChild(address);

		return container;
	};
};

EmailPickerField.prototype._onLoadSuggestions = function () {
	var that = this;
	return function (listPanel, data) {
		var replacementText = {
			"%NUMBER%": listPanel.getItems().length,
			"%TEXT%": that._lastQuery.query
		};
		//listPanel.setTitle(listPanel.getItems().length + " suggestion(s) for \"" + that._lastQuery.query + "\"");
		listPanel.setTitle(translate("LBL_PMSE_EMAILPICKER_RESULTS_TITLE").replace(/%\w+%/g, function(wildcard) {
		   return replacementText[wildcard] || wildcard;
		}));
	};
};

EmailPickerField.prototype._createPanel = function () {
	var that = this;
	if (!this._teamsPanel) {
		this._teamsPanel = new ListPanel({
			id: "list-teams",
			title: translate('LBL_PMSE_EMAILPICKER_TEAMS'),
			itemsContent: function(item, data) {
				return data[that._teamNameField] || "";
			}
		});
		this.setTeams(this._teams);
	}
	if (!this._suggestPanel) {
		this._suggestPanel = new ListPanel({
			id: "list-suggest",
			title: translate('LBL_PMSE_EMAILPICKER_SUGGESTIONS'),
			itemsContent: this._suggestionItemContent(),
			visible: false,
			bodyHeight: 150,
			onLoad: this._onLoadSuggestions()
		});
	}
	/*if (!this._fieldsPanel) {
		this._fieldsPanel = new ListPanel({
			id: "list-fields",
			title: "Module Fields",
			bodyHeight: 200,
			onExpand: function (listPanel) {
				listPanel.setDataURL(that._relatedModulesFieldsDataURL)
					.setDataRoot(that._relatedModulesFieldsDataRoot)
					.load();
			}
		});
	}*/
	this._panel = new FieldPanel({
		items: [
			{
				type: 'button',
				value: "Current User",
				text: translate('LBL_PMSE_EMAILPICKER_CURRENT_USER')
			}, 
			{
				type: 'button',
				value: "Record Owner",
				text: translate('LBL_PMSE_EMAILPICKER_RECORD_OWNER')
			}, 
			{
				type: "button", 
				value: "Supervisor",
				text: translate('LBL_PMSE_EMAILPICKER_SUPERVISOR')
			},
			this._teamsPanel,/*
			this._fieldsPanel,*/
			this._suggestPanel
		]
	});
	MultipleItemField.prototype._createPanel.call(this);
	return this;
};

EmailPickerField.prototype._isValidInput = function () {
	var that = this;
	return function (itemContainer, text) {
		return /^\s*[\w\-\+_]+(\.[\w\-\+_]+)*\@[\w\-\+_]+\.[\w\-\+_]+(\.[\w\-\+_]+)*\s*$/.test(text);
	};
};

EmailPickerField.prototype._loadSuggestions = function (c) {
	var that = this;
	return function	() {
		var url;
		clearInterval(that._timer);
		if(that._suggestionDataURL) {
			that._lastQuery = {
				query: c,
				dataRoot: that._suggestionDataRoot,
				dataURL: that._suggestionDataURL
			};
			url = that._suggestionDataURL.replace(/\{\$\d+\}/g, encodeURIComponent(c));
			that._suggestPanel.setDataURL(url)
				.setDataRoot(that._suggestionDataRoot);
			that._suggestPanel.load();
		}
	};
};

EmailPickerField.prototype._showSuggestionPanel = function () {
	var panelItems = this._panel.getItems(), i;

	if (!this._suggestionVisible) {
		for (i = 0; i < panelItems.length; i += 1) {
			if (panelItems[i] !== this._suggestPanel) {
				panelItems[i].setVisible(false);
			} else {
				panelItems[i].setVisible(true);
			}
		}	
	}
	this._suggestionVisible = true;
	return this;
};

EmailPickerField.prototype._hideSuggestionPanel = function () {
	var panelItems = this._panel.getItems(), i;

	if (this._suggestionVisible) {
		for (i = 0; i < panelItems.length; i += 1) {
			if (panelItems[i] !== this._suggestPanel) {
				panelItems[i].setVisible(true);
			} else {
				panelItems[i].setVisible(false);
			}
		}	
	}
	this._suggestionVisible = false;
	return this;
};

EmailPickerField.prototype._onInputChar = function () {
	var that = this;
	return function (itemContainer, theChar, completeText, keyCode) {
		var trimmedText = jQuery.trim(completeText);
		clearInterval(that._timer);
		if (trimmedText) {
			if (that._suggestionDataURL) {
				//Vefify if the current query is identical than the last one
				if (!(that._lastQuery.query === trimmedText && that._lastQuery.dataURL === that._suggestionDataURL 
					&& that._lastQuery.dataRoot === that._suggestionDataRoot)) {
					that._timer = setInterval(that._loadSuggestions(trimmedText), that._delaySuggestTime);
					that._suggestPanel.clearItems()
						._showLoadingMessage()
						.setTitle(translate("LBL_PMSE_EMAILPICKER_SUGGESTIONS"));
				}
				that._showSuggestionPanel();
				that.openPanel(true);
				that._suggestPanel.expand();
			}/* else {
				that.openPanel();
			}*/
		} else {
			that._hideSuggestionPanel();
		}
	};
};

EmailPickerField.prototype.openPanel = function (showSuggestionPanel) {
	if (!showSuggestionPanel) {
		this._hideSuggestionPanel();
	}
	return MultipleItemField.prototype.openPanel.call(this);
};

EmailPickerField.prototype.createHTML = function () {
	if(!this.html) {
		MultipleItemField.prototype.createHTML.call(this);
		this.controlObject.setOnInputCharHandler(this._onInputChar());
	}
	return this;
};
