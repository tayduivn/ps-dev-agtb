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

describe('NotificationCenter.Field.AddressBase', function() {
    var app;
    var field;
    var sandbox;
    var module = 'NotificationCenter';
    var fieldType = 'address-base';
    var layout;
    var carriers = {foo: {options: {deliveryDisplayStyle: 'select'}, status: true}};
    var addressTypeOptions = {'0': 'email1', '1': 'email2'};
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
        SugarTest.testMetadata.set();
        field = SugarTest.createField('base', 'dummy', fieldType, 'edit', {}, module, model, null, true);
        field.carrier = 'foo';
        field.def.options = {deliveryDisplayStyle: 'select'};
        field.items = addressTypeOptions;
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

    describe('render()', function() {
        it('should call showHideField() on render', function() {
            sandbox.stub(field, 'getFormattedValue');
            var method = sandbox.stub(field, 'showHideField');
            field.render();
            expect(method).toHaveBeenCalled();
        });
    });

    describe('showHideField()', function() {
        var global;
        var personal;

        beforeEach(function() {
            global = {foo: {status: null}};
            personal = {foo: {status: null}};
        });

        using('statues, isConfigured and action',
            [
                [true, true, true, 'show'],
                [false, false, false, 'hide'],
                [true, true, false, 'hide'],
                [false, true, true, 'hide'],
                [true, false, true, 'hide'],
                [true, false, false, 'hide'],
                [false, false, true, 'hide'],
                [false, true, false, 'hide']
            ],
            function(globalStatus, personalStatus, isConfigured, action) {
                it('should show or hide field according to carrier status & configuration state', function() {
                    var method = sandbox.stub(field, action);
                    global.foo.status = globalStatus;
                    global.foo.isConfigured = isConfigured;
                    personal.foo.status = personalStatus;
                    model.set('global', {carriers: global});
                    model.set('personal', {carriers: personal});
                    field.showHideField();
                    expect(method).toHaveBeenCalled();
                });
            });
    });

    describe('getFormattedValue()', function() {
        using('selectedAddresses and value',
            [
                [
                    ['0', '1'],
                    [{checked: true, id: '0', label: 'email1'}, {checked: true, id: '1', label: 'email2'}]
                ],
                [
                    ['0'],
                    [{checked: true, id: '0', label: 'email1'}, {checked: false, id: '1', label: 'email2'}]
                ]
            ],
            function(selectedAddresses, value) {
                it('should call format() with correct value', function() {
                    var format = sandbox.stub(field, 'format');
                    model.set('selectedAddresses', {'foo': selectedAddresses});
                    field.getFormattedValue();
                    expect(format).toHaveBeenCalledWith(value);
                });
            });
    });

    describe('setSelectedAddresses()', function() {
        beforeEach(function() {
            field.model.set('selectedAddresses', {foo: ['0']});
        });

        it('should set selectedAddresses to model in case one address is given', function() {
            field.setSelectedAddresses('1');
            expect(model.get('selectedAddresses')).toEqual({foo: ['1']});
        });

        it('should set selectedAddresses to model in case list of address is given', function() {
            field.setSelectedAddresses(['0', '1', '2']);
            expect(model.get('selectedAddresses')).toEqual({foo: ['0', '1', '2']});
        });

        it('should not affect selected addresses of other carriers' , function() {
            field.model.set('selectedAddresses', {foo: ['0'], bar: ['0', '1']});
            field.setSelectedAddresses(['0']);
            expect(model.get('selectedAddresses')).toEqual({foo: ['0'], bar: ['0', '1']});
        });
    });
});
