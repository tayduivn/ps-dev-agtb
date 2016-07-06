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

describe('NotificationCenter.Field.AddressRadio', function() {
    var app;
    var field;
    var sandbox;
    var module = 'NotificationCenter';
    var fieldType = 'address-radio';
    var layout;
    var carriers = {foo: {options: {deliveryDisplayStyle: 'radio'}, status: true}};
    var addressTypeOptions = {0: '0', 1: '1', 2: '2'};
    var model;
    var fieldDef = {
        name: 'dummy',
        type: fieldType,
        view: 'edit'
    };

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
        SugarTest.loadComponent('base', 'field', 'address-base', module);
        SugarTest.loadHandlebarsTemplate(fieldType, 'field', 'base', 'edit', module);
        SugarTest.testMetadata.set();
        field = SugarTest.createField('base', 'dummy', fieldType, 'edit', fieldDef, module, model, null, true);
        field.carrier = 'foo';
        field.def.options = {deliveryDisplayStyle: 'radio'};
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

    describe('bindDomChange()', function() {
        afterEach(function() {
            field.model.get('personal').selectedCarriersOptions = {foo: []};
        });

        it('should set checked addresses from selectedCarriersOptions of a model', function() {
            field.model.get('personal').selectedCarriersOptions = {foo: []};
            field.render();
            field.$(field.fieldTag).eq(0).click().trigger('change');
            expect(model.get('personal').selectedCarriersOptions).toEqual({foo: ['0']});
        });
    });
});
