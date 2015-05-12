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
var ExpressionContainer = function (options, parent) {
    Element.call(this, options);
    //this.isCBOpen = null;
    //this.isDDOpen = null;
    this.tooltipHandler = null;
    this.expression = null;
    this.value = null;
    this.parent = null;
    this.onChange = null;
    this.onBeforeOpenPanel = null;
    ExpressionContainer.prototype.init.call(this, options, parent);
};

ExpressionContainer.prototype = new Element();

ExpressionContainer.prototype.type = 'ExpressionContainer';

ExpressionContainer.prototype.family = 'ExpressionContainer';

ExpressionContainer.prototype.unsupportedDataTypes = [
    'Encrypt',
    'IFrame',
    'Image',
    'MultiSelect',
    'FlexRelate',
    'Relate'
];

ExpressionContainer.prototype.init = function (options, parent) {
    var defaults = {
        expression: [],
        onBeforeOpenPanel: null,
        onChange: null
    };
    $.extend(true, defaults, options);
    this.setExpressionValue(defaults.expression)
        //.setIsCBOpen(defaults.isCBOpen)
        //.setIsDDOpen(defaults.isDDOpen)
        .setParent(parent)
        .setOnBeforeOpenPanel(defaults.onBeforeOpenPanel)
        .setOnChangeHandler(defaults.onChange);
};

ExpressionContainer.prototype.setOnBeforeOpenPanel = function (handler) {
    if (!(handler === null || typeof handler === 'function')) {
        throw new Error("setOnBeforeOpenPanel(): The parameter must be a function or null.");
    }
    this.onBeforeOpenPanel = handler;
    return this;
};

ExpressionContainer.prototype.setOnChangeHandler = function(handler) {
    if (!(handler === null || typeof handler === 'function')) {
        throw new Error("setOnChangeHandler(): The parameter must be a function or null.");
    }
    this.onChange = handler;
    return this;
};

ExpressionContainer.prototype.setExpressionValue = function (value) {
    this.expression = value;
    this.updateExpressionView();
    return this;
};

//ExpressionContainer.prototype.setIsCBOpen = function (value) {
//    this.isCBOpen = value;
//    return this;
//};
//
//ExpressionContainer.prototype.setIsDDOpen = function (value) {
//    this.isDDOpen = value;
//    return this;
//};

ExpressionContainer.prototype.setParent = function (parent) {
    this.parent = parent;
    return this;
};

ExpressionContainer.prototype.clear = function () {
    return this;
};

//ExpressionContainer.prototype.addItem = function (value) {
//    console.log('AddItem method was called.' + this.id, value);
//    this.setExpressionValue(value);
//    return this;
//};

ExpressionContainer.prototype.remove = function () {
    $(this.html).remove();
    delete this.tooltipHandler;
    delete this.expression;
    delete this.value;
    delete this.parent;
};

ExpressionContainer.prototype.getObject = function () {
    return this.expression;
};

ExpressionContainer.prototype.isValid = function () {
    return true;
};

ExpressionContainer.prototype.createHTML = function () {
    var dvContainer,
        span;

    if(this.html) {
        return this.html;
    }

    span = this.createHTMLElement('span');
    dvContainer = this.createHTMLElement("div");
    dvContainer.className = 'expression-container-cell';
    $(dvContainer).attr('data-placement', 'bottom');
    dvContainer.setAttributeNode(document.createAttribute('title'));

    span.appendChild(dvContainer);
    this.html = span;
    this.dvContainer = dvContainer;

    this.updateExpressionView();
    this.attachListeners();

    return this.html;
};

ExpressionContainer.prototype.updateExpressionView = function () {
    var value = this.parseValue(this.expression),
        $container;

    $container = $(this.dvContainer);
    $container.text(value);
    $container.attr('data-original-title', value);

    return this;
};

ExpressionContainer.prototype.parseValue = function (expression) {
    var val = '';
    if (expression && expression.length) {
        table = this.parent.parent.parent
        for (i = 0; i < expression.length; i += 1) {
            if (val !== '') {
                val += ' ';
            }
            val += table.globalCBControl.getLabel(expression[i]);
        }
    }
    return val;
};

ExpressionContainer.prototype.attachListeners = function () {
    var self = this;

    if(!this.html) {
        return this;
    }

    //Define Tooltip when ellipsis overflow is active
    $(this.dvContainer).on('mouseenter', function () {
        if (this.offsetWidth < this.scrollWidth) {
            this.tooltipHandler = $(this).tooltip({trigger:'manual'});
            this.tooltipHandler.tooltip('show');
        }
    }).on("mouseleave", function() {
        if (this.tooltipHandler) {
            this.tooltipHandler.tooltip('destroy');
            this.tooltipHandler = null;
        }
    });

    //Define click events to handle CriteriaBuilderControl
    $(this.html).on('click', function () {
        self.handleClick(this);
    });

    return this;
};

ExpressionContainer.prototype.handleClick = function (element) {
    var globalParent,
        parentVariable;

    globalParent = this.parent.parent.parent;
    parentVariable = this.parent.parent;

    if (parentVariable.fieldType || parentVariable.isReturnType) {
        if (parentVariable.fieldType === 'DropDown' || parentVariable.fieldType === 'Checkbox') {
            this.handleDropDownBuilder(globalParent, parentVariable, element);
        } else {
            this.handleCriteriaBuilder(globalParent, parentVariable, element);
        }
    } else {
        App.alert.show('expression-variable-click', {
            level: 'warning',
            messages: translate('LBL_PMSE_MESSAGE_LABEL_DEFINE_COLUMN_TYPE', 'pmse_Business_Rules'),
            autoClose: true
        });
    }
};

ExpressionContainer.prototype.handleCriteriaBuilder = function (globalParent, parentVariable, element) {
    var self = this,
        value,
        defaults = {
            operators: false,
            evaluations: false,
            variables: false,
            constants: false
        },
        config = {};

    if (globalParent.globalCBControl.isOpen()) {
        globalParent.globalCBControl.close();
        //this.setIsCBOpen(false);
    } else {
        globalParent.globalCBControl.setOwner(element);
        globalParent.globalCBControl.setOnChangeHandler(function (expressionControl, newValue, oldValue) {
            value = JSON.parse(newValue);
            self.setExpressionValue(value);
            if (typeof self.onChange === 'function') {
                self.onChange(newValue, oldValue);
            }
        });
        if (parentVariable.isReturnType) {
            config = {
                constants: true,
                variables: {
                    dataRoot: null,
                    data: parentVariable.fields,
                    dataFormat: "tabular",
                    textField: "label",
                    moduleTextField: "moduleText",
                    moduleValueField: "moduleValue"
                }
            };
        } else {
            if(this.unsupportedDataTypes.indexOf(parentVariable.fieldType) >= 0) {
                App.alert.show('expression-variable-unsupported-data-type', {
                    level: 'warning',
                    messages: translate('LBL_PMSE_MESSAGE_LABEL_UNSUPPORTED_DATA_TYPE', 'pmse_Business_Rules'),
                    autoClose: true
                });
                return;
            }
            switch (parentVariable.fieldType) {
                case 'Date':
                case 'Datetime':
                    config = {
                        operators: {
                            arithmetic: ["+","-"]
                        },
                        constants: {
                            date: parentVariable.fieldType === 'Date' ? true : false,
                            datetime: parentVariable.fieldType === 'Datetime' ? true : false,
                            timespan: true
                        },
                        variables: {
                            dataRoot: null,
                            data: parentVariable.fields,
                            dataFormat: "tabular",
                            textField: "label",
                            typeFilter: parentVariable.fieldType,
                            moduleTextField: "moduleText",
                            moduleValueField: "moduleValue"
                        }
                    };
                    break;
                case 'TextArea':
                case 'TextField':
                case 'email':
                case 'Phone':
                case 'URL':
                    $.extend(true, config, {
                        constants: {
                            basic: {
                                string: true
                            }
                        },
                        variables: {
                            dataRoot: null,
                            data: parentVariable.fields,
                            dataFormat: "tabular",
                            textField: "label",
                            typeFilter: parentVariable.fieldType,
                            moduleTextField: "moduleText",
                            moduleValueField: "moduleValue"
                        }
                    });
                    if (parentVariable.variableMode === 'conclusion' && !parentVariable.isReturnType
                        && parentVariable.fieldType === 'email') {
                        config.variables.typeFilter = function (type, data) {
                            if (parentVariable.fieldType !== type) {
                                return false;
                            }
                            return data.value !== 'email1'
                        };
                    }
                    break;
                case 'Integer':
                case 'Currency':
                    $.extend(true, config, {
                        operators: {
                            arithmetic: true,
                            group: true
                        },
                        constants: {
                            basic: {
                                number: true
                            }
                        },
                        variables: {
                            dataRoot: null,
                            data: parentVariable.fields,
                            dataFormat: "tabular",
                            textField: "label",
                            typeFilter: parentVariable.fieldType,
                            moduleTextField: "moduleText",
                            moduleValueField: "moduleValue"
                        }
                    });
                    break;
                default:
                    if (parentVariable.isReturnType) {
                        $.extend(true, config, {
                            constants: {
                                basic: true,
                                date: true
                            }
                        });
                    } else {
                        $.extend(true, config, {
                            constants: {
                                basic: {
                                    string: true
                                }
                            },
                            variables: {
                                dataRoot: null,
                                data: parentVariable.fields,
                                dataFormat: "tabular",
                                textField: "label",
                                typeFilter: parentVariable.fieldType,
                                moduleTextField: "moduleText",
                                moduleValueField: "moduleValue"
                            }
                        });
                    }
                    break;
            }
        }

        $.extend(true, defaults, config);
        //globalParent.globalCBControl.clear();
        globalParent.globalCBControl
            .setOperators(defaults.operators)
            .setEvaluations(defaults.evaluations)
            .setVariablePanel(defaults.variables)
            .setConstantPanel(defaults.constants);
        globalParent.globalCBControl.setValue(this.expression);
        if (typeof this.onBeforeOpenPanel === 'function') {
            this.onBeforeOpenPanel(this);
        }
        globalParent.globalCBControl.open();
        //this.setIsCBOpen(true);
    }
};

ExpressionContainer.prototype.handleDropDownBuilder = function (globalParent, parentVariable, element) {
    var self = this,
        value;
    if (globalParent.globalDDSelector.isOpen()) {
        globalParent.globalDDSelector.close();
        //this.setIsDDOpen(false);
    } else {
        globalParent.globalDDSelector.setOwner(element);
        globalParent.globalDDSelector.setOnItemValueActionHandler(function (dropdownSelector, list, obj) {
            var prevValue = JSON.stringify(self.expression);
            if (Object.keys(obj).length === 0) {
                value = [];
            } else {
                value = [{
                    expType: "CONSTANT",
                    expSubType: "string",
                    expLabel: obj.text,
                    expValue: obj.value
                }];
            }
            self.setExpressionValue(value);
            globalParent.globalDDSelector.close();

            if (typeof self.onChange === 'function') {
                self.onChange(JSON.stringify(self.expression), prevValue);
            }
            //self.setIsDDOpen(false);
        });
        globalParent.globalDDSelector.setValues(parentVariable.combos[parentVariable.module + globalParent.moduleFieldSeparator
            + parentVariable.field]);
        globalParent.globalDDSelector.setValue(this.expression);
        if (typeof this.onBeforeOpenPanel === 'function') {
            this.onBeforeOpenPanel(this);
        }
        globalParent.globalDDSelector.open();
        //this.setIsDDOpen(true);
    }
};

