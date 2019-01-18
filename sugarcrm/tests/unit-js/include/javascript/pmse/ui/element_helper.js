//FILE SUGARCRM flav=ent ONLY
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
describe('includes.javascript.pmse.ui.element_helper', function() {

    var helper;

    afterEach(function() {
        sinon.collection.restore();
    });

    describe('init', function() {

        it('Check ElementHelper initialization', function() {
            var params = {
                mode: 'EmailPickerField',
                decimalSeparator: ',',
                numberGroupingSeparator: '.'
            };
            helper = new PMSE.ElementHelper(params);
            helper.OPERATORS.comparison = [
                {
                    value: 'equals'
                }
            ];
            helper.OPERATORS.changes = [
                {
                    value: 'changes_to'
                }
            ];
            expect(helper._decimalSeparator).toEqual(params.decimalSeparator);
            expect(helper._numberGroupingSeparator).toEqual(params.numberGroupingSeparator);
            expect(PROJECT_MODULE).toEqual('Leads');
        });

    });

    describe('fieldDependencyHandler', function() {

        var module = new FormPanelDropdown({
            name: 'module',
            dependantFields: ['field', 'emailAddressField']
        });
        var field = new FormPanelDropdown({
            name: 'field',
            disabled: true
        });
        var email = new FormPanelDropdown({
            name: 'emailAddressField',
            disabled: true
        });
        var ret = {type: 'many'};

        beforeEach(function() {
            sinon.collection.stub(FormPanelDropdown.prototype, 'getSelectedData').returns(ret);
            sinon.collection.stub(PMSE.ElementHelper.prototype, 'loadFieldControl');
        });

        it('Check logic of disabling the "field" field', function() {
            helper.fieldDependencyHandler(field, module, '');
            expect(field._disabled).toEqual(true);
            helper.fieldDependencyHandler(field, module, 'Leads');
            expect(field._disabled).toEqual(true);
            ret.type = 'one';
            helper.fieldDependencyHandler(field, module, 'Accounts');
            expect(field._disabled).toEqual(true);
            ret.type = 'many';
            helper.fieldDependencyHandler(field, module, 'Accounts');
            expect(field._disabled).toEqual(false);
        });

        it('Check logic of not disabling the "emailAddressField" field', function() {
            helper.fieldDependencyHandler(email, module, '');
            expect(email._disabled).toEqual(false);
            helper.fieldDependencyHandler(email, module, 'Leads');
            expect(email._disabled).toEqual(false);
            ret.type = 'one';
            helper.fieldDependencyHandler(email, module, 'Accounts');
            expect(email._disabled).toEqual(false);
            ret.type = 'many';
            helper.fieldDependencyHandler(email, module, 'Accounts');
            expect(email._disabled).toEqual(false);
        });

    });

    describe('valueDependencyHandler', function() {
        var field = new FormPanelDropdown({
            name: 'field',
            disabled: true,
            dependantFields: ['value']
        });
        var operator = new FormPanelDropdown({
            name: 'operator',
            disabled: true,
            dependantFields: ['value']
        });
        var value = new FormPanelText({
            name: 'value',
            disabled: true
        });
        var ret = {operator: operator, value: value};

        beforeEach(function() {
            sinon.collection.stub(PMSE.ElementHelper.prototype, 'doValueDependency').returns(ret);
        });

        it('Check logic of disabling the "operator" and "value" fields', function() {
            helper.valueDependencyHandler(value, field, 'Created By');
            expect(operator._disabled).toEqual(true);
            expect(value._disabled).toEqual(true);
            field.enable();
            helper.valueDependencyHandler(value, field, 'Created By');
            expect(operator._disabled).toEqual(false);
            expect(value._disabled).toEqual(false);
        });
    });

    describe('relatedDependencyHandler', function() {

        var module = new FormPanelDropdown({
            name: 'module',
            dependantFields: ['related']
        });
        var related = new FormPanelDropdown({
            name: 'related',
            disabled: true
        });

        beforeEach(function() {
            sinon.collection.stub(PMSE.ElementHelper.prototype, 'loadRelatedControl');
        });

        it('Check logic of disabling the "related" field', function() {
            helper.relatedDependencyHandler(related, module, '');
            expect(related._disabled).toEqual(true);
            helper.relatedDependencyHandler(related, module, 'Leads');
            expect(related._disabled).toEqual(true);
            helper.relatedDependencyHandler(related, module, 'Accounts');
            expect(related._disabled).toEqual(false);
        });

    });

    describe('processValueDependency', function() {
        var parentField = new FormPanelDropdown({
            _name: 'field'
        });
        var dependantField = new FormPanelText({
            _name: 'value',
            _label: 'Value',
        });
        var operatorField = new FormPanelDropdown({
            _name: 'operator'
        });
        operatorField.html = {innerHTML: '<div/>'};
        operatorField._htmlControl[0] = document.createElement('select');
        var form = new FormPanel();
        var checkOperator = function(operatorField, operator) {
            var op = false;
            if (operatorField._htmlControl && operatorField._htmlControl[0]) {
                for (i = 0; i < operatorField._htmlControl[0].length; i++) {
                    if (operatorField._htmlControl[0][i].value === operator) {
                        op = true;
                        break;
                    }
                }
            }
            return op;
        };
        var ret = new FormPanelDate();
        beforeEach(function() {
            sinon.collection.stub(FormPanel.prototype, '_createField').returns(ret);
        });
        it('Check to include changes_to operators for date/datetime fields', function() {
            helper._parent = new ExpressionControl({});
            helper._parent._name = 'evn_criteria';
            helper.processValueDependency(dependantField, parentField, operatorField, 'date', null, form);
            var operatorExist = checkOperator(operatorField, 'changes_to');
            expect(operatorExist).toEqual(true);

            helper._parent._name = 'pro_terminate_variables';
            helper.processValueDependency(dependantField, parentField, operatorField, 'datetime', null, form);
            operatorExist = checkOperator(operatorField, 'changes_to');
            expect(operatorExist).toEqual(true);
        });
    });

});
