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
var MultipleItemField = function (settings, parent) {
	Field.call(this, settings, parent);
	this._panel = null;
	this._onValueAction = null;
	this._panelAppended = false;
	this._panelSemaphore = false;
	this._proxy = new SugarProxy();
};

MultipleItemField.prototype = new Field();
MultipleItemField.prototype.constructor = MultipleItemField;
MultipleItemField.prototype.type = "MultipleItemField";
/**
 * The function which processes the text for the items to be added to the field.
 * @abstract
 * @return {Function|null} The function which must return a string or an HTML Element to be used as the text for the 
 * items to be added.
 */
MultipleItemField.prototype._onItemSetText = function () {
	return function () {
		return "[MultipleItemField Item]";
	};
};

MultipleItemField.prototype._createItemData = function (rawData) {
	return rawData;
};

MultipleItemField.prototype._createItem = function (data, usableItem) {
	var newItem;

	if(usableItem instanceof SingleItem) {
		newItem = usableItem;
	} else {
		newItem = new SingleItem();
	}
	newItem.setFullData(this._createItemData(data));
	newItem.setText(this._onItemSetText());
	return newItem;
};

MultipleItemField.prototype.addItem = function (item, noFocus) {
	this.controlObject.addItem(this._createItem(item), null, noFocus);
	return this;
};

MultipleItemField.prototype._setValueToControl = function (value) {
	var i;
	value = value || [];
	value = typeof value ===  'string' ? JSON.parse(value) : value;
	if (!jQuery.isArray(value)) {
		throw new Error("setValue(): The parameter is incorrectly formatted.");
	}
	for (i = 0; i < value.length; i += 1) {
		this.addItem(value[i], true);
	}
	return this;
};

MultipleItemField.prototype._onChange = function () {
	var that = this;
	return function	() {
		var newValue = that._getValueFromControls(), currentValue = that.value;
		if(newValue !== currentValue) {
			that.value = newValue;
			that.onChange(that.value, currentValue);
		}
	};
};

MultipleItemField.prototype.isPanelOpen = function () {
	return this._panel.isOpen();
};

MultipleItemField.prototype.openPanel = function () {
	var parent;
	if (!this.isPanelOpen()) {
		this._panel.open();
		this.controlObject.style.addClasses(['focused']);
		this._panel.style.addClasses(['focused']);
	}
	return this;
};

MultipleItemField.prototype.closePanel = function () {
	this._panel.close();
	this.controlObject.style.removeClasses(['focused']);
	this._panel.style.removeClasses(['focused']);
	return this;
};

MultipleItemField.prototype._getValueFromControls = function () {
	var value = this.controlObject.getData();
	return JSON.stringify(value);
};
/**
 * Valid the text input.
 * @abstract
 * @return {Function|null} The function must return true or false.
 */
MultipleItemField.prototype._isValidInput = function () {
	return null;
};
/**
 * Actions to perform before add an item by text input.
 * @abstract
 * @return {Function|null} The function to execute before the new item be added.
 */
MultipleItemField.prototype._onBeforeAddItemByInput = function () {
	return null;
};
/**
 * Action to perform when the panel fires a value action.
 * @abstract
 * @return {Function|null} The function to be executed when a panel's value action occurs.
 */
MultipleItemField.prototype._onPanelValueGeneration = function () {
	return null;
};

MultipleItemField.prototype.getObject = function () {
	var i, items = this.controlObject.getItems(), obj = [];
	for (i = 0; i < items.length; i += 1) {
		obj.push(items[i].getData());
	}
	return obj;
};

MultipleItemField.prototype._createPanel = function () {
	var that = this;
	if (this.html) {
		if(!this._panel) {
			throw new Error("_createPanel(): This method must be called from an overwritten _createdMethod() method in any subclasses after creatinf the panel.");
		} else if(!(this._panel instanceof FieldPanel)) {
			throw new Error("_createPanel(): The panel created must be an instance of FieldPanel.");
		}
		this._panel.setAppendTo(function () {
			var parent = (that.parent && that.parent.parent) || null;
			return parent ? parent.html : document.body;
		});
		this._panel.setOwner(this.controlObject).close();
		this._panel.setOnItemValueActionHandler(this._onPanelValueGeneration());
	}
	return this;
};

MultipleItemField.prototype.scrollTo = function () {
    var fieldsDiv = this.html.parentNode, 
    	scrollForControlObject = getRelativePosition(this.controlObject.html, fieldsDiv).top + $(this.controlObject.html).outerHeight() + fieldsDiv.scrollTop,
    	that = this;
    if (fieldsDiv.scrollTop + $(fieldsDiv).outerHeight() < scrollForControlObject) {
        jQuery(this.html.parentNode).animate({
        	scrollTop: scrollForControlObject
        }, function() {
        	that.openPanel();
        });
        return;
    }

    return this;
};

MultipleItemField.prototype._attachListeners = function () {
	var that = this;
	if(this.html) {
		jQuery(this._panel.getHTML()).on('mousedown', function (e) {
			e.stopPropagation();
			//that.controlObject.select();
			that._panelSemaphore = true;
		});

		$(this.parent && this.parent.body).on('scroll', function () {
			that.closePanel();
		});
	}
	return this;
};

MultipleItemField.prototype.evalRequired = function () {
	var response = true, value;
    if (this.required) {
        response = !!this.controlObject.getItems().length;
        if (!response) {
            this.controlObject.style.addClasses(['required']);//$(this.controlObject).addClass('required');
        } else {
            this.controlObject.style.removeClasses(['required']);//$(this.controlObject).removeClass('required');
        }
    }
    return response;
};

MultipleItemField.prototype.clear = function () {
	if (this.controlObject) {
		this.controlObject.clearItems();
	}
	this.value = this._getValueFromControls();
	this.isValid();
	return this;
};

MultipleItemField.prototype._createItemContainer = function () {
	var itemsContainer, that = this;
	if (!this.controlObject) {
		itemsContainer = new ItemContainer({
	    	className: "adam-field-control",
	    	onAddItem: this._onChange(),
	    	onRemoveItem: this._onChange(),
	    	width: this.fieldWidth || 200,
	    	textInputMode: ItemContainer.prototype.textInputMode.ALL,
	    	inputValidationFunction: this._isValidInput(),
	    	onBeforeAddItemByInput: this._onBeforeAddItemByInput(),
	    	onBlur: function() {
	    		if (!that._panelSemaphore) {
	    			that.closePanel();
	    		} /*else {
	    			this.select(this.getSelectedIndex());
	    		}*/
	    		that._panelSemaphore = false;
	    	},
	    	onFocus: function() {
	    		that.scrollTo();
	    		if(!that._panel.isOpen()) {
	    			that.openPanel();
	    		}
	    	}
	    });
	    this.controlObject = itemsContainer;
	    this._setValueToControl(this.value);
	}
	return this;
};

MultipleItemField.prototype.createHTML = function () {
	var fieldLabel, required = '', readAtt, that = this;
	if (!this.html) {
	    Field.prototype.createHTML.call(this);

	    if (this.required) {
	        required = '<i>*</i> ';
	    }

	    fieldLabel = this.createHTMLElement('span');
	    fieldLabel.className = 'adam-form-label';
	    fieldLabel.innerHTML = this.label + ': ' + required;
	    fieldLabel.style.width = (this.parent && this.parent.labelWidth) || "30%";
	    fieldLabel.style.verticalAlign = 'top';
	    this.html.appendChild(fieldLabel);

	    if (this.readOnly) {
	        //TODO: implement readOnly!!!!!
	    }
	    this._createItemContainer().html.appendChild(this.controlObject.getHTML());

	    this._createPanel();

	    if (this.errorTooltip) {
	        this.html.appendChild(this.errorTooltip.getHTML());
	    }
	    if (this.helpTooltip) {
	        this.html.appendChild(this.helpTooltip.getHTML());
	    }

	    this.labelObject = fieldLabel;
	    this._attachListeners();
	}
	return this.html;
};