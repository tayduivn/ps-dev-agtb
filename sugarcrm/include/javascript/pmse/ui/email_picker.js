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
	this._teamsDropdown = null;
	this._teamTextField = null;
	this._teamValueField = null;
	this._lastQuery = null;
	this._roles = null;
	this._rolesDropdown = null;
	this._roleTextField = null;
	this._roleValueField = null;
	this._modules = null;
	this._moduleTextField = null;
	this._moduleValueField = null;
	this._userModules = null;
	this._recipientModules = null;
	EmailPickerField.prototype.init.call(this, settings);
};

EmailPickerField.prototype = new MultipleItemField();
EmailPickerField.prototype.constructor = EmailPickerField;
EmailPickerField.prototype.type = 'EmailPickerField';

EmailPickerField.prototype.init = function (settings) {
	var defaults = {
		teams: [],
		roles: [],
		modules: [],
		teamTextField: "text",
		teamValueField: "value",
		roleTextField: "text",
		roleValueField: "value",
		moduleTextField: "text",
		moduleValueField: "value"
	};

	jQuery.extend(true, defaults, settings);

	this._lastQuery = {};

	this.setTeamTextField(defaults.teamTextField)
		.setTeamValueField(defaults.teamValueField)
		.setTeams(defaults.teams)
		.setRoleTextField(defaults.roleTextField)
		.setRoleValueField(defaults.roleValueField)
		.setRoles(defaults.roles)
		.setModuleTextField(defaults.moduleTextField)
		.setModuleValueField(defaults.moduleValueField)
		.setModules(defaults.modules);
};

EmailPickerField.prototype.setModuleTextField = function (field) {
	if (typeof field !== 'string') {
		throw new Error("setModuleTextField(): The parameter must be a string.");
	}
	this._moduleTextField = field;
	return this;
};

EmailPickerField.prototype.setModuleValueField = function (field) {
	if (typeof field !== 'string') {
		throw new Error("setModuleValueField(): The parameter must be a string.");
	}
	this._moduleValueField = field;
	return this;
};

EmailPickerField.prototype.setModules = function (items) {
	var i;
	if(!jQuery.isArray(items)) {
		throw new Error("setModules(): The parameter must be an array.");
	}
	this._modules = items;
	if(this._userModules) {
		this._userModules.setOptions(items);
		this._recipientModules.setOptions(items);
	}
	return this;
};

EmailPickerField.prototype.setRoleTextField = function(field) {
	if (typeof field !== 'string') {
		throw new Error("setRoleTextField(): The parameter must be a string.");
	}
	this._roleTextField = field;
	return this;
};

EmailPickerField.prototype.setRoleValueField = function(field) {
	if (typeof field !== 'string') {
		throw new Error("setRoleValueField(): The parameter must be a string.");
	}
	this._roleValueField = field;
	return this;
};

EmailPickerField.prototype.setRoles = function (items) {
	var i;
	if(!jQuery.isArray(items)) {
		throw new Error("setRoles(): The parameter must be an array.");
	}
	this._roles = items;
	if(this._rolesDropdown) {
		this._rolesDropdown.setOptions(items);
	}
	return this;
};

EmailPickerField.prototype.setTeamTextField = function(teamTextField) {
	if (typeof teamTextField !== 'string') {
		throw new Error("setTeamTextField(): The parameter must be a string.");
	}
	this._teamTextField = teamTextField;
	return this;
};

EmailPickerField.prototype.setTeamValueField = function (teamValueField) {
	if (typeof teamValueField !== 'string') {
		throw new Error("setTeamValueField(): The parameter must be a string.");
	}
	this._teamValueField = teamValueField;
	return this;
};

EmailPickerField.prototype.setTeams = function (teams) {
	var i;
	if(!jQuery.isArray(teams)) {
		throw new Error("setItems(): The parameter must be an array.");
	}
	this._teams = teams;
	if(this._teamsDropdown) {
		this._teamsDropdown.setOptions(teams);
	}
	return this;
};

EmailPickerField.prototype._onItemSetText = function () {
	return function(itemObject, data) {
		return data.label;
	};
};

/*EmailPickerField.prototype._createItemData = function(data) {
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
};*/

EmailPickerField.prototype._onPanelValueGeneration = function () {
	var that = this;
	return function (fieldPanel, fieldPanelItem, data) {
		var newEmailItem = {}, parentPanelID = that.id, i18nID, aux = 'i18n', replacementText;

		switch (fieldPanelItem.id) {
			case parentPanelID + '-user-form':
				newEmailItem.type = 'user';
				newEmailItem.module = data['module'];
				newEmailItem.value = data['user_who'];
				newEmailItem.user = data['user'];
				i18nID = 'LBL_PMSE_EMAILPICKER_'
					+ fieldPanelItem.getItem('user').getSelectedData(aux) + "_"
					+ fieldPanelItem.getItem('user_who').getSelectedData(aux);

				newEmailItem.label = translate(i18nID).replace(/%\w+%/g,
					fieldPanelItem.getItem("module").getSelectedText());
				break;
			case parentPanelID + '-recipient-form':
				newEmailItem.type = 'recipient';
				newEmailItem.module = data['module'];
				newEmailItem.value = data['emailAddressField'];
				newEmailItem.label = fieldPanelItem.getItem('module').getSelectedText() + ":"
					+ fieldPanelItem.getItem('emailAddressField').getSelectedText();
				break;
			case parentPanelID + '-team-form':
				newEmailItem.type = 'team';
				newEmailItem.value = data['team'];
				newEmailItem.label = translate('LBL_PMSE_EMAILPICKER_TEAM_ITEM').replace(/%\w+%/g,
					fieldPanelItem.getItem("team").getSelectedText());
				break;
			case parentPanelID + '-role-form':
				newEmailItem.type = 'role';
				newEmailItem.value = data['role'];
				newEmailItem.label = translate('LBL_PMSE_EMAILPICKER_ROLE_ITEM').replace(/%\w+%/g,
					fieldPanelItem.getItem("role").getSelectedText());
				break;
			default:
				throw new Error('_onPanelValueGeneration(): invalid fieldPanelItem\'s id.');
		}
		newEmailItem = that._createItem(newEmailItem);
		that.controlObject.addItem(newEmailItem);
	};
};

EmailPickerField.prototype._createPanel = function () {
	var that = this;

	if (!this._panel) {
		this._teamsDropdown = new FormPanelDropdown({
			name: 'team',
			label: 'Team',
			type: 'dropdown',
			required: true,
			width: '100%',
			labelField: this._teamTextField,
			valueField: this._teamValueField
		});
		this.setTeams(this._teams);

		this._rolesDropdown = new FormPanelDropdown({
			name: 'role',
			label: 'Role',
			type: 'dropdown',
			width: '100%',
			required: true,
			labelField: this._roleTextField,
			valueField: this._roleValueField
		});
		this.setRoles(this._roles);

		this._userModules = new FormPanelDropdown({
			name: 'module',
			label: 'Module',
			type: 'dropdown',
			required: true,
			width: '100%',
			labelField: this._moduleTextField,
			valueField: this._moduleValueField
		});
		this._recipientModules = new FormPanelDropdown({
			name: 'module',
			label: 'Module',
			type: 'dropdown',
			required: true,
			width: '100%',
			labelField: this._moduleTextField,
			valueField: this._moduleValueField,
			dependantFields: ['emailAddressField']
		});
		this.setModules(this._modules);

		this._panel = new FieldPanel({
			items: [
				{
					type: "multiple",
					headerVisible: false,
					collapsed: false,
					bodyHeight: 117,
					items: [
						{
							id: this.id + "-user-form",
							type: 'form',
							title: "User",
							items: [
								this._userModules,
								{
									name: 'user',
									label: '',
									type: 'dropdown',
									required: true,
									width: '40%',
									options: [
										{
											label: 'User who',
											value: 'who',
											i18n: "USER"
										},
										{
											label: 'User is manager of who',
											value: 'manager_of',
											i18n: "MANAGER"
										}
									]
								},
								{
									name: 'user_who',
									label: '',
									type: 'dropdown',
									required: true,
									width: '60%',
									options: [
										{
											label: 'created the record',
											value: 'record_creator',
											i18n: 'CREATED'
										},
										{
											label: 'last modified the record',
											value: 'last_modifier',
											i18n: 'LAST_MODIFIED'
										},
										{
											label: 'is assigned to the record',
											value: 'is_assignee',
											i18n: 'IS_ASSIGNED'
										},
										{
											label: 'was assigned to the record',
											value: 'was_assignee',
											i18n: 'WAS_ASSIGNED'
										}
									]
								}
							]
						},
						{
							id: this.id + "-recipient-form",
							type: 'form',
							title: "Recipient",
							items: [
								this._recipientModules,
								{
									name: 'emailAddressField',
									label: 'Email Address Field',
									type: 'dropdown',
									required: true,
									width: '100%',
									dependencyHandler: function (dependantField, field, value) {
										var relatedModule;
										if (!value) {
											return;
										}
										relatedModule = App.metadata.getRelationship(value);
										dependantField.setDataURL('pmse_Project/CrmData/fields/' + value)
											.setDataRoot('result')
											.setLabelField('text')
											.setValueField('value')
											.load();
									},
									optionsFilter: function (item) {
										return item.type === "email";
									}
								}
							]
						},
						{
							id: this.id + '-team-form',
							type: 'form',
							title: translate('LBL_PMSE_EMAILPICKER_TEAMS'),
							required: true,
							items: [
								this._teamsDropdown
							]
						},
						{
							id: this.id + '-role-form',
							type: 'form',
							title: "Role",
							items: [
								this._rolesDropdown
							]
						}
					]
				}
			]
		});
		MultipleItemField.prototype._createPanel.call(this);
	}

	return this;
};

EmailPickerField.prototype._isValidInput = function () {
	return function (itemContainer, text) {
		return false;
	};
};

EmailPickerField.prototype._createItemContainer = function () {
	return MultipleItemField.prototype._createItemContainer.call(this, {
		textInputMode: ItemContainer.prototype.textInputMode.END
	});
};
