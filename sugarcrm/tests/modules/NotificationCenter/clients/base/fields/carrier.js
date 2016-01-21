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

describe('NotificationCenter.Field.Carrier', function() {
    var app, field, sandbox,
        module = 'NotificationCenter',
        fieldName = 'foo',
        fieldType = 'carrier',
        fieldDef,
        layout,
        carriers,
        model;

    beforeEach(function() {
        app = SugarTest.app;
        sandbox = sinon.sandbox.create();
        carriers = {
            foo: {
                status: false,
                isConfigured:false
            }
        };
        SugarTest.testMetadata.init();
        SugarTest.declareData('base', module, true, false);
        layout = SugarTest.createLayout('base', module, 'config-drawer', null, null, true);
        model = layout.model;
        model.set('configMode', 'global');
        model.set('carriers', carriers);
        fieldDef = {
            name: fieldName,
            type: 'carrier',
            view: 'default'
        };
        SugarTest.loadHandlebarsTemplate(fieldType, 'field', 'base', 'default', module);
        SugarTest.testMetadata.set();
        field = SugarTest.createField('base', fieldName, fieldType, 'default', fieldDef, module, model, null, true);
    });

    afterEach(function() {
        if (field) {
            field.dispose();
        }
        Handlebars.templates = {};
        SugarTest.testMetadata.dispose();
        sandbox.restore();
        app.cache.cutAll();
        app.view.reset();
        layout = null;
        model = null;
        field = null;
    });

    it('should set up "carriers" property for admin mode', function() {
        field = SugarTest.createField('base', fieldName, fieldType, 'default', fieldDef, module, model, null, true);
        expect(field.carriers).toBe(carriers);
    });

    it('should set up "carriers" property for user mode', function() {
        model.set('configMode', 'user');
        model.set('personal', {carriers: carriers});
        model.set('global', {carriers: carriers});
        field = SugarTest.createField('base', fieldName, fieldType, 'default', fieldDef, module, model, null, true);
        expect(field.carriers).toBe(carriers);
    });

    it('should set up "carriersGlobal" property for user mode', function() {
        model.set('configMode', 'user');
        model.set('personal', {carriers: carriers});
        model.set('global', {carriers: carriers});
        field = SugarTest.createField('base', fieldName, fieldType, 'default', fieldDef, module, model, null, true);
        expect(field.carriersGlobal).toBe(carriers);
    });

    describe('format()', function() {
        it('should return false if carrier is disabled', function() {
            field.carriers['foo'] = {status: false};
            expect(field.format()).toBeFalsy();
        });

        it('should return true if carrier is enabled', function() {
            field.carriers['foo'] = {status: true};
            expect(field.format()).toBeTruthy();
        });

        it('should not set up isGloballyEnabled if no global carriers are found in model', function() {
            field.carriersGlobal = null;
            field.format();
            expect(field.def.isGloballyEnabled).toBeUndefined();
        });

        it('should set isGloballyEnabled to true if carrier is globally enabled', function() {
            field.carriersGlobal = {
                foo: {status: true, configurable: false}
            };
            field.format();
            expect(field.def.isGloballyEnabled).toBeTruthy();
        });

        it('should set isGloballyEnabled to false if carrier is globally disabled', function() {
            field.carriersGlobal = {
                foo: {status: false, configurable: true, isConfigured: true}
            };
            field.format();
            expect(field.def.isGloballyEnabled).toBeFalsy();
        });
        it('should set isGloballyEnabled to false if carrier is not configured', function () {
            field.carriersGlobal = {
                foo: {status: true, configurable: true, isConfigured: false}
            };
            field.format();
            expect(field.def.isGloballyEnabled).toBeFalsy();
        });
        it('should set isGloballyEnabled to true if carrier is configured', function () {
            field.carriersGlobal = {
                foo: {status: true, configurable: true, isConfigured: true}
            };
            field.format();
            expect(field.def.isGloballyEnabled).toBeTruthy();
        });
    });

    describe('bindDomChange()', function() {
        using('values list',
            [true, false],
            function(value) {
                it('should set carrier status properly', function() {
                    field.render();
                    field.$(field.fieldTag + '[name=foo]').prop('checked', value).trigger('change');
                    expect(model.get('carriers').foo.status).toEqual(value);
                });
            });

        using('mode-events list',
            [
                ['user', 'change:personal:carrier:foo'],
                ['global', 'change:carrier:foo'],
                ['user', 'change:personal:carrier'],
                ['global', 'change:carrier']
            ],
            function(mode, event) {
                it('should cause model to trigger event', function() {
                    field.model.set('configMode', mode);
                    var modelEvent = sandbox.spy(field.model, 'trigger');
                    field.render();
                    field.$(field.fieldTag + '[name=foo]').trigger('change');
                    expect(modelEvent).toHaveBeenCalledWith(event);
                });
            });
    });
});
