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

describe('NotificationCenter.Field.AddressSelect', function() {
    var app;
    var field;
    var sandbox;
    var module = 'NotificationCenter';
    var fieldType = 'address-select';
    var fieldDef;
    var layout;
    var carriers = {foo: {options: {deliveryDisplayStyle: 'select'}, status: true}};
    var model;

    beforeEach(function() {
        app = SugarTest.app;
        sandbox = sinon.sandbox.create();
        SugarTest.testMetadata.init();
        SugarTest.declareData('base', module, true, false);
        layout = SugarTest.createLayout('base', module, 'config-drawer', null, null, true);
        model = layout.model;
        model.set('configMode', 'user');
        model.set('global', {carriers: carriers});
        model.set('personal', {carriers: carriers});
        fieldDef = {
            name: 'dummy',
            type: fieldType,
            view: 'edit'
        };
        SugarTest.loadComponent('base', 'field', 'address-base', module);
        SugarTest.loadHandlebarsTemplate(fieldType, 'field', 'base', 'edit', module);
        SugarTest.testMetadata.set();
        field = SugarTest.createField('base', 'dummy', fieldType, 'edit', fieldDef, module, model, null, true);
        field.carrier = 'foo';
    });

    afterEach(function() {
        if (field) {
            field.dispose();
        }
        SugarTest.testMetadata.dispose();
        Handlebars.templates = {};
        sandbox.restore();
        app.cache.cutAll();
        layout = null;
        model = null;
        field = null;
    });

    describe('getFormattedValue()', function() {
        using('selectedAddresses and value',
            [
                [{foo: ['email1']}, {id: 'email1', key: 0}],
                [{foo: []}, []]
            ],
            function(selectedAddresses, value) {
                it('should call format() with correct value', function() {
                    var format = sandbox.stub(field, 'format');
                    model.set('selectedAddresses', selectedAddresses);
                    field.getFormattedValue();
                    expect(format).toHaveBeenCalledWith(value);
                });
            });
    });

    describe('setValue()', function() {
        using('value, address',
            [
                ['1', ['1']],
                [1, [1]]
            ],
            function(value, address) {
                it('should call _updateModelAndTriggerChange() with correct value', function() {
                    var method = sandbox.stub(field, '_updateModelAndTriggerChange');
                    field.setValue(value);
                    expect(method).toHaveBeenCalledWith(address);
                });
            });

        using('value',
            [
                [null],
                [undefined]
            ],
            function(value) {
                it('should not call _updateModelAndTriggerChange() with incorrect values', function() {
                    var method = sandbox.stub(field, '_updateModelAndTriggerChange');
                    field.setValue(value);
                    expect(method).not.toHaveBeenCalled();
                });
            });
    });

    describe('_updateModelAndTriggerChange()', function() {
        beforeEach(function() {
            field.model.set('selectedAddresses', {'foo': []});
        });

        it('should call render()', function() {
            var render = sandbox.stub(field, 'render');
            field._updateModelAndTriggerChange([]);
            expect(render).toHaveBeenCalled();
        });

        using('set value, expected selectedAddresses list',
            [
                [[], {foo: []}],
                [['email1'], {foo: ['email1']}],
                [['email1', 'email2'], {foo: ['email1', 'email2']}]
            ],
            function(value, selectedAddresses) {
                it('should populate model\'s selectedAddresses', function() {
                    field._updateModelAndTriggerChange(value);
                    expect(field.model.get('selectedAddresses')).toEqual(selectedAddresses);
                });
            });
    });
});
